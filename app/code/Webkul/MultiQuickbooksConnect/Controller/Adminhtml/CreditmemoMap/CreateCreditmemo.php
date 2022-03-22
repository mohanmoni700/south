<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml\CreditmemoMap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MultiQuickbooksConnect\Helper\Data as QuickBooksHelperData;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks as QuickBooksHelper;
use Webkul\MultiQuickbooksConnect\Model\OrderMapFactory;
use Webkul\MultiQuickbooksConnect\Model\CreditmemoMapFactory;
use Webkul\MultiQuickbooksConnect\Logger\Logger;

class CreateCreditmemo extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelperData;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\QuickBooksHelperData
     */
    private $quickBooksHelperData;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\QuickBooks
     */
    private $quickBooksHelper;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Model\OrderMapFactory
     */
    private $orderMapFactory;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Model\CreditmemoMapFactory
     */
    private $creditmemoMapFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonHelperData $jsonHelperData
     * @param TimezoneInterface $timezone
     * @param OrderRepositoryInterface $orderRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param ProductRepositoryInterface $productRepository
     * @param QuickBooksHelperData $quickBooksHelperData
     * @param QuickBooksHelper $quickBooksHelper
     * @param OrderMapFactory $orderMapFactory
     * @param CreditmemoMapFactory $creditmemoMapFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonHelperData $jsonHelperData,
        TimezoneInterface $timezone,
        OrderRepositoryInterface $orderRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        ProductRepositoryInterface $productRepository,
        QuickBooksHelperData $quickBooksHelperData,
        QuickBooksHelper $quickBooksHelper,
        OrderMapFactory $orderMapFactory,
        CreditmemoMapFactory $creditmemoMapFactory,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelperData = $jsonHelperData;
        $this->timezone = $timezone;
        $this->orderRepository = $orderRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->productRepository = $productRepository;
        $this->quickBooksHelperData = $quickBooksHelperData;
        $this->quickBooksHelper = $quickBooksHelper;
        $this->orderMapFactory = $orderMapFactory;
        $this->creditmemoMapFactory = $creditmemoMapFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $accountId = $this->getRequest()->getParam('account_id');

        if ($data['creditmemo_id']) {
            $config = $this->quickBooksHelperData->getQuickbooksAccountConfig($accountId);
            if ($config['enable']) {
                $creditmemo = $this->creditmemoRepository->get($data['creditmemo_id']);
                $order = $creditmemo->getOrder();
                if ($order && $order->getId()) {
                    $taxPercent = $this->quickBooksHelperData->getOrderTaxPercent($order->getEntityId());
                    $result = [];
                    $allItems = $creditmemo->getAllItems();
                    $items = [];
                    foreach ($allItems as $creditmemoItem) {
                        $orderItem = $creditmemoItem->getOrderItem();
                        $product = $orderItem->getProduct();
                        $typeId = $product ? $product->getTypeId() : $orderItem->getProductType();
                        if (in_array($typeId, ['simple', 'virtual', 'downloadable', 'etickets'])) {
                            $itemData = $this->quickBooksHelperData
                                ->getArrangedItemDataForQuickbooks($orderItem, $taxPercent, $creditmemoItem->getQty());
                            array_push($items, $itemData);
                        }
                    }

                    if ($order->getShippingMethod()) {
                        $taxAmount = $creditmemo->getShippingAddress() ?
                                            $creditmemo->getShippingAddress()->getData('tax_amount') : 0;
                        $itemData = [
                            'Name' => $order->getShippingDescription().__('Shipping'),
                            'UnitPrice' => $creditmemo->getBaseShippingAmount(),
                            'Qty' => 1,
                            'Sku' => str_replace(" ", "", $order->getShippingDescription()),
                            'isTaxablePro' => 0,
                            'Taxable' => $taxAmount || (isset($taxPercent[''][0]) ?
                                                        true : false ) ? 1 : 0, // "" index for shipping tax
                            'discountAmt' => $order->getBaseShippingDiscountAmount(),
                            'AmountTotal' => $creditmemo->getBaseShippingAmount(),
                            'Type' => 'Service',
                            'TrackQtyOnHand' => 'false',
                            'ItemId' => '' // '' for shipping itemid
                        ];
                        array_push($items, $itemData);
                    }

                    $customerData = $this->quickBooksHelperData->getCustomerDetailForQuickbooks($order);
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
                        ->addFieldToFilter('quickbook_creditmemo_doc_number', $creditmemoReceiptData['docNumber'])
                        ->setPageSize(1)->getFirstItem();
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
                        } else {
                            $result['msg'] = $result['msg'].__(" Credit Memo Id - "). $creditmemo->getIncrementId();
                        }
                    } else {
                        $result['error'] = 1;
                        $result['msg'] = 'Credit Memo #'.$creditmemo->getIncrementId().__(' already mapped.');
                    }
                } else {
                    $result['error'] = 1;
                    $result['msg'] = 'Order for Credit Memo #'.$creditmemo->getIncrementId().__(' not found.');
                }
            } else {
                $result = ['error' => 1, 'msg' => __('Currently module is disabled.')];
            }
            $this->getResponse()->representJson($this->jsonHelperData->jsonEncode($result));
        } else {
            $data = $this->getRequest()->getParams();
            $total = (int) $data['count']+1 - (int) $data['skip'];
            $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ')
                        .$total.__(' Credit memo(s) Exported to Quickbooks.').'</div>';
            $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
            $result['msg'] = $msg;
            $this->getResponse()->representJson($this->jsonHelperData->jsonEncode($result));
        }
    }

    /**
     * Check creditmemo import permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MultiQuickbooksConnect::creditmemo_import');
    }
}
