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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks as QuickBooksHelper;
use Webkul\MultiQuickbooksConnect\Helper\Data as QuickBooksDataHelper;
use Webkul\MultiQuickbooksConnect\Model\CreditmemoMapFactory;
use Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface;
use Webkul\MultiQuickbooksConnect\Logger\Logger;

/**
 * Webkul MultiQuickbooksConnect SalesOrderCreditmemoSaveAfterObserver Observer Model.
 */
class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
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
     * @var Webkul\MultiQuickbooksConnect\Model\CreditmemoMapFactory
     */
    private $creditmemoMapFactory;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Api\AccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var Webkul\MultiQuickbooksConnect\Logger\Logger
     */
    private $logger;

    /**
     * @param QuickBooksHelper $quickBooksHelper,
     * @param QuickBooksDataHelper $quickBooksDataHelper,
     * @param CreditmemoMapFactory $creditmemoMapFactory,
     * @param Logger $logger
     */
    public function __construct(
        QuickBooksHelper $quickBooksHelper,
        QuickBooksDataHelper $quickBooksDataHelper,
        CreditmemoMapFactory $creditmemoMapFactory,
        AccountRepositoryInterface $accountRepository,
        Logger $logger
    ) {
        $this->quickBooksHelper = $quickBooksHelper;
        $this->quickBooksDataHelper = $quickBooksDataHelper;
        $this->creditmemoMapFactory = $creditmemoMapFactory;
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
            $creditmemo = $observer->getCreditmemo();
            $order = $creditmemo->getOrder();
            $qbAccount = $this->accountRepository->getByStoreId($order->getStoreId());
            if ($qbAccount) {
                $accountId = $qbAccount->getId();
                $config = $this->quickBooksDataHelper->getQuickbooksAccountConfig($accountId);
                $accessToken = $this->quickBooksDataHelper->getAccessToken($accountId);
                if ($config['enable'] && $config['creditmemo_auto_sync'] && $accessToken) {
                    $taxPercent = $this->quickBooksDataHelper->getOrderTaxPercent($order->getEntityId());
                    $allItems = $creditmemo->getAllItems();
                    $items = [];
                    foreach ($allItems as $creditmemoItem) {
                        $orderItem = $creditmemoItem->getOrderItem();
                        $quantityAndStockStatus = $orderItem->getProduct()->getQuantityAndStockStatus();
                        $typeId = $orderItem->getProduct()->getTypeId();
                        if (in_array($typeId, ['simple', 'virtual', 'downloadable', 'etickets'])) {
                            $itemData = $this->quickBooksDataHelper
                                ->getArrangedItemDataForQuickbooks($orderItem, $taxPercent, $creditmemoItem->getQty());
                            array_push($items, $itemData);
                        }
                    }
                    if ($order->getShippingMethod()) {
                        $taxAmount = $order->getBaseShippingTaxAmount() ? $order->getBaseShippingTaxAmount() : 0;
                        $itemData = [
                            'Name' => $order->getShippingDescription(). ' ('.__('Shipping data').')',
                            'UnitPrice' => $creditmemo->getBaseShippingAmount(),
                            'Taxable' => $taxAmount || (isset($taxPercent[''][0]) ?
                                                        true : false ) ? 1 : 0, // "" index for shipping tax
                            'taxAmt' => $taxAmount,
                            'Sku' => str_replace(" ", "", $order->getShippingDescription()),
                            'isTaxablePro' => 0,
                            'Qty' => 1,
                            'discountAmt' => 0,
                            'AmountTotal' => $creditmemo->getBaseShippingAmount(),
                            'Type' => 'Service',
                            'TrackQtyOnHand' => 'false',
                            'ItemId' => '' // '' for shipping itemid
                        ];
                        array_push($items, $itemData);
                    }

                    $customerData = $this->quickBooksDataHelper->getCustomerDetailForQuickbooks($order);
                    $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
                    $creditmemoReceiptData = [
                        'items' => $items,
                        'customerData' => $customerData,
                        'discount_on_creditmemo' => $creditmemo->getBaseDiscountAmount(),
                        'tax_percent' => $taxPercent,
                        'paymentMethod' => $paymentMethod ? $paymentMethod : __('Magento Store Payment'),
                        'docNumber' => 'cmemo-'.$creditmemo->getIncrementId()
                    ];

                    $creditmemoReceipt = $this->creditmemoMapFactory->create()->getCollection()
                                            ->addFieldToFilter(
                                                'quickbook_creditmemo_doc_number',
                                                $creditmemoReceiptData['docNumber']
                                            )->setPageSize(1)->getFirstItem();
                    if (!$creditmemoReceipt->getEntityId()) {
                        $result = $this->quickBooksHelper->createCreditMemo($creditmemoReceiptData, $accountId);
                        if ($result['error'] == 0) {
                            $creditmemoReceipt = $result['creditmemoReceiptData'];
                            $data = [
                                'mage_creditmemo_id' => $creditmemo->getEntityId(),
                                'quickbook_creditmemo_id' => $creditmemoReceipt->Id,
                                'quickbook_creditmemo_doc_number' => $creditmemoReceipt->DocNumber,
                                'account_id' => $accountId
                            ];
                            $mapObj = $this->creditmemoMapFactory->create();
                            $mapObj->setData($data);
                            $mapObj->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError('SalesOrderCreditmemoRefundObserver : '.$e->getMessage());
        }
    }
}
