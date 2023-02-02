<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_QuickbookCustomInv
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\QuickbookCustomInv\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks as QuickBooksHelper;
use Webkul\MultiQuickbooksConnect\Helper\Data as QuickBooksDataHelper;
use Webkul\MultiQuickbooksConnect\Model\OrderMapFactory;
use Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface;
use Webkul\MultiQuickbooksConnect\Logger\Logger;

/**
 * Webkul MultiQuickbooksConnect SalesOrderSaveCommitAfterObserver Observer Model.
 */
class SalesOrderSaveCommitAfterObserver implements ObserverInterface
{
    /**
     * @var Webkul\MultiQuickbooksConnect\Helper\QuickBooks
     */
    private $quickBooksHelper;

    /**
     * @var Webkul\MultiQuickbooksConnect\Helper\Data
     */
    private $quickBooksDataHelper;

    /**
     * @var Webkul\MultiQuickbooksConnect\Model\OrderMapFactory
     */
    private $orderMapFactory;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var Webkul\MultiQuickbooksConnect\Logger\Logger
     */
    private $logger;

    /**
     * @param QuickBooksHelper $quickBooksHelper
     * @param QuickBooksDataHelper $quickBooksDataHelper
     * @param OrderMapFactory $orderMapFactory
     * @param AccountRepositoryInterface $accountRepository
     * @param Logger $logger
     */
    public function __construct(
        QuickBooksHelper $quickBooksHelper,
        QuickBooksDataHelper $quickBooksDataHelper,
        OrderMapFactory $orderMapFactory,
        AccountRepositoryInterface $accountRepository,
        Logger $logger
    ) {
        $this->quickBooksHelper = $quickBooksHelper;
        $this->quickBooksDataHelper = $quickBooksDataHelper;
        $this->orderMapFactory = $orderMapFactory;
        $this->accountRepository = $accountRepository;
        $this->logger = $logger;
    }

    /**
     * Sales order save commmit after on order complete state event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance Order */
        try {
            $orderList = $observer->getOrders();
            $orderList = $orderList ? $orderList : [$observer->getOrder()];
            foreach ($orderList as $order) {
                $qbAccount = $this->accountRepository->getByStoreId($order->getStoreId());
                if ($qbAccount) {
                    $accountId = $qbAccount->getId();
                    $config = $this->quickBooksDataHelper->getQuickbooksAccountConfig($accountId);
                    $accessToken = $this->quickBooksDataHelper->getAccessToken($accountId);
                    if ($config['enable'] && $config['sales_receipt_create_on']=='order_place' && $accessToken) {
                        $this->prepareSalesReceipt($order, $accountId);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('SalesOrderSaveCommitAfterObserver : '.$e->getMessage());
        }
    }

    /**
     * Prepare Sales Receipt
     *
     * @param object $order
     * @param integer $accountId
     * @return void
     */
    private function prepareSalesReceipt($order, $accountId)
    {
        $taxPercent = $this->quickBooksDataHelper->getOrderTaxPercent($order->getEntityId());
        $allItems = $order->getAllItems();
        $items = [];
        $discountTotal = 0;
        foreach ($allItems as $orderItem) {
            $typeId = $orderItem->getProduct()->getTypeId();
            if (in_array($typeId, ['simple', 'virtual', 'downloadable', 'etickets'])) {
                $itemData = $this->quickBooksDataHelper
                                    ->getArrangedItemDataForQuickbooks($orderItem, $taxPercent);
                array_push($items, $itemData);
            }
        }

        if ($order->getShippingMethod()) {
            $taxAmount = $order->getBaseShippingTaxAmount() ? $order->getBaseShippingTaxAmount() : 0;
            $itemData = [
                'Name' => $order->getShippingDescription().__('Shipping'),
                'UnitPrice' => $order->getBaseShippingAmount(),
                'Taxable' => $taxAmount || (isset($taxPercent[''][0]) ?
                                            true : false ) ? 1 : 0, // "" index for shipping tax
                'taxAmt' => $taxAmount,
                'Sku' => str_replace(" ", "", $order->getShippingDescription()),
                'isTaxablePro' => 0,
                'Qty' => 1,
                'discountAmt' => $order->getBaseShippingDiscountAmount(),
                'AmountTotal' => $order->getBaseShippingAmount(),
                'Type' => 'Service',
                'TrackQtyOnHand' => 'false',
                'ItemId' => '' // '' for shipping itemid
            ];
            array_push($items, $itemData);
        }

        /** tax as item **/
        $appliedTax = $this->quickBooksDataHelper->getAppliedTaxOnOrder($order);
        $items = empty($appliedTax) ? $items : array_merge($items, $appliedTax);
        /** tax as item **/
        $customerData = $this->quickBooksDataHelper->getCustomerDetailForQuickbooks($order);
        /** for export shipping info **/
        $tracksCollection = $order->getTracksCollection()->getItems();
        $trackingList = [];
        $shipServiceList = [];
        $shipDate = date('Y-m-d', time());
        foreach ($tracksCollection as $trackingData) {
            if ($trackingData->getTrackNumber()) {
                $trackingList[] = $trackingData->getTrackNumber();
                $shipServiceList[] = $trackingData->getTitle();
                $shipDate = date('Y-m-d', strtotime($trackingData->getCreatedAt()));
            }
        }
        /** for export shipping info **/
        $salesReceiptData = [
            'items' => $items,
            'customerData' => $customerData,
            'discount_on_order' => $order->getBaseDiscountAmount(),
            'tax_percent' => $taxPercent,
            'paymentMethod' => $order->getPayment()->getMethodInstance()->getTitle(),
            'docNumber' => 'order-'.$order->getIncrementId(),
            'mageOrderId' => $order->getIncrementId(),
            'tracking_info' => substr(implode(",", $trackingList), 0, 31),
            'ship_service' => substr(implode(",", $shipServiceList), 0, 31),
            'shipDate' => $shipDate
        ];
        $salesReceipt = $this->orderMapFactory->create()->getCollection()
                ->addFieldToFilter(
                    'quickbook_sales_doc_number',
                    $salesReceiptData['docNumber']
                )->setPageSize(1)->getFirstItem();
        if (!$salesReceipt->getEntityId()) {
            $salesReceipt = $this->quickBooksHelper->createSalesReceipt($salesReceiptData, $accountId);
            if ($salesReceipt['error'] == 0) {
                $salesReceipt = $salesReceipt['salesReceiptData'];
                $data = [
                    'mage_order_id' => $order->getEntityId(),
                    'mage_invoice_id' => 'N/A',
                    'quickbook_sales_receipt_id' => $salesReceipt->Id,
                    'quickbook_sales_doc_number' => $salesReceipt->DocNumber,
                    'account_id' => $accountId
                ];
                $mapObj = $this->orderMapFactory->create();
                $mapObj->setData($data);
                $mapObj->save();
            }
        }
    }
}
