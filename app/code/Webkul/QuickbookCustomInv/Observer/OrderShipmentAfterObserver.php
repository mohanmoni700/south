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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks as QuickBooksHelper;
use Webkul\MultiQuickbooksConnect\Helper\Data as QuickBooksDataHelper;
use Webkul\MultiQuickbooksConnect\Model\OrderMapFactory;
use Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface;
use Webkul\MultiQuickbooksConnect\Logger\Logger;

/**
 * Webkul MultiQuickbooksConnect SalesOrderInvoiceSaveAfterObserver Model.
 */
class OrderShipmentAfterObserver implements ObserverInterface
{
    /**
     * @var Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Webkul\MultiQuickbooksConnect\Helper\QuickBooks
     */
    private $quickBooksHelper;

    /**
     * @var Webkul\MultiQuickbooksConnect\Model\OrderMapFactory
     */
    private $orderMapFactory;

    /**
     * @var Webkul\MultiQuickbooksConnect\Helper\Data
     */
    private $quickBooksDataHelper;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var Webkul\MultiQuickbooksConnect\Logger\Logger
     */
    private $logger;

    /**
     * @param RequestInterface $requestInterface,
     * @param OrderRepositoryInterface $orderRepository,
     * @param QuickBooksHelper $quickBooksHelper,
     * @param OrderMapFactory $orderMapFactory,
     * @param QuickBooksDataHelper $quickBooksDataHelper,
     * @param Logger $logger
     */
    public function __construct(
        RequestInterface $requestInterface,
        OrderRepositoryInterface $orderRepository,
        QuickBooksHelper $quickBooksHelper,
        OrderMapFactory $orderMapFactory,
        QuickBooksDataHelper $quickBooksDataHelper,
        AccountRepositoryInterface $accountRepository,
        Logger $logger
    ) {
        $this->requestInterface = $requestInterface;
        $this->orderRepository = $orderRepository;
        $this->quickBooksHelper = $quickBooksHelper;
        $this->orderMapFactory = $orderMapFactory;
        $this->quickBooksDataHelper = $quickBooksDataHelper;
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
        try {
            $shipment = $observer->getEvent()->getShipment();
            $tracksCollection = $shipment->getTracksCollection()->getItems();
            $trackingList = [];
            $shipServiceList = [];
            $shipDate = date('Y-m-d', time());
            foreach ($tracksCollection as $trackingData) {
                if ($trackingData->getTrackNumber()) {
                    $trackingList[] = $trackingData->getTrackNumber();
                    $shipServiceList[] = $trackingData->getTitle();
                    $date = $trackingData->getCreatedAt() ? $trackingData->getCreatedAt() : '';
                    $shipDate = date('Y-m-d', strtotime($date));
                }
            }
            /*if ($shipment->getOrigData('entity_id')) {
                return;
            }*/
            //$invoice = $observer->getInvoice();
            $order = $this->orderRepository->get($shipment->getOrderId());
            $qbAccount = $this->accountRepository->getByStoreId($order->getStoreId());
            if ($qbAccount) {
                $accountId = $qbAccount->getId();
                $config = $this->quickBooksDataHelper->getQuickbooksAccountConfig($accountId);
                $accessToken = $this->quickBooksDataHelper->getAccessToken($accountId);
                if ($config['enable'] && $accessToken) {
                    $taxPercent = $this->quickBooksDataHelper->getOrderTaxPercent($order->getEntityId());
                    $allItems = $order->getAllItems();
                    $items = [];
                    //$invoiceItems = $this->requestInterface->getParam('invoice');
                    foreach ($allItems as $orderItem) {
                        $typeId = $orderItem->getProduct()->getTypeId();
                        $itemId = $orderItem->getParentItemId() ?
                                        $orderItem->getParentItemId() : $orderItem->getItemId();
                        $orderedQty = $orderItem->getQtyOrdered();
                        $parentOrderedQty = $orderItem->getParentItem() ?
                                        $orderItem->getParentItem()->getQtyOrdered() : $orderedQty;
                        //$qty = isset($invoiceItems['items'][$itemId]) ? $invoiceItems['items'][$itemId] : 0;
                        $qty = $orderedQty;
                        if (in_array($typeId, ['simple', 'virtual', 'downloadable', 'etickets']) && $qty) {
                            $qty = $qty * ($orderedQty/$parentOrderedQty);
                            $itemData = $this->quickBooksDataHelper
                                                ->getArrangedItemDataForQuickbooks($orderItem, $taxPercent, $qty);
                            array_push($items, $itemData);
                        } else {
                            $this->logger->addError(__('invoice event type - %1 : qty - %2', $typeId, $qty));
                        }
                    }

                    $this->logger->addError(__('invoice event items - %1 : ', json_encode($items)));
                    $customerData = $this->quickBooksDataHelper->getCustomerDetailForQuickbooks($order);

                    if ($order->getShippingAmount()) {
                        $taxAmount = $order->getBaseShippingTaxAmount() ? $order->getBaseShippingTaxAmount() : 0;
                        $itemData = [
                            'Name' => $order->getShippingDescription(). ' ('.__('Shipping').')',
                            'UnitPrice' => $order->getBaseShippingAmount(),
                            'Taxable' => $taxAmount || (isset($taxPercent[''][0]) ?
                                                        true : false ) ? 1 : 0, // "" index for shipping tax
                            'taxAmt' => $taxAmount,
                            'Sku' => str_replace(" ", "", $order->getShippingDescription()),
                            'isTaxablePro' => 0,
                            'Qty' => 1,
                            'AmountTotal' => $order->getBaseShippingAmount(),
                            'discountAmt' => 0,
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
                                                'mage_order_id',
                                                $order->getEntityId()
                                            )->setPageSize(1)->getFirstItem();
                    if (!$salesReceipt->getEntityId() && 0) {
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
                    } elseif ($salesReceipt->getEntityId()) {
                        $salesReceiptData['quickbook_sales_receipt_id'] = $salesReceipt->getQuickbookSalesReceiptId();
                        $salesReceipt = $this->quickBooksHelper->updateSalesReceipt($salesReceiptData, $accountId);
                        if ($salesReceipt['error'] == 0) {
                            $this->logger->addError(__('QB order updated : %1', $salesReceiptData['quickbook_sales_receipt_id']));
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('OrderShipmentAfterObserver : '.$e->getMessage());
        }
    }
}
