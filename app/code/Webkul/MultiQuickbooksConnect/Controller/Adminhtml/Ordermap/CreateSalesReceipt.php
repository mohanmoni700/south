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
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml\Ordermap;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MultiQuickbooksConnect\Helper\Data as QuickBooksHelperData;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks as QuickBooksHelper;
use Webkul\MultiQuickbooksConnect\Model\OrderMapFactory;
use Webkul\MultiQuickbooksConnect\Logger\Logger;

class CreateSalesReceipt extends \Magento\Backend\App\Action
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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonHelperData $jsonHelperData
     * @param TimezoneInterface $timezone
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param QuickBooksHelperData $quickBooksHelperData
     * @param QuickBooksHelper $quickBooksHelper
     * @param OrderMapFactory $orderMapFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonHelperData $jsonHelperData,
        TimezoneInterface $timezone,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        QuickBooksHelperData $quickBooksHelperData,
        QuickBooksHelper $quickBooksHelper,
        OrderMapFactory $orderMapFactory,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelperData = $jsonHelperData;
        $this->timezone = $timezone;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->quickBooksHelperData = $quickBooksHelperData;
        $this->quickBooksHelper = $quickBooksHelper;
        $this->orderMapFactory = $orderMapFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $accountId = $this->getRequest()->getParam('account_id');

        if ($data['order_id']) {
            $config = $this->quickBooksHelperData->getQuickbooksAccountConfig($accountId);
            if ($config['enable']) {
                $order = $this->orderRepository->get($data['order_id']);
                $taxPercent = $this->quickBooksHelperData->getOrderTaxPercent($order->getEntityId());
                $result = [];
                $allItems = $order->getAllItems();
                $items = [];
                foreach ($allItems as $orderItem) {
                    $product = $orderItem->getProduct();
                    $typeId = $product ? $product->getTypeId() : $orderItem->getProductType();
                    if (in_array($typeId, ['simple', 'virtual', 'downloadable', 'etickets'])) {
                        $itemData = $this->quickBooksHelperData
                                            ->getArrangedItemDataForQuickbooks($orderItem, $taxPercent);
                        array_push($items, $itemData);
                    }
                }

                if ($order->getShippingMethod()) {
                    $taxAmount = $order->getShippingAddress() ? $order->getShippingAddress()->getData('tax_amount') : 0;
                    $itemData = [
                        'Name' => $order->getShippingDescription().__('Shipping'),
                        'UnitPrice' => $order->getBaseShippingAmount(),
                        'Qty' => 1,
                        'Sku' => str_replace(" ", "", $order->getShippingDescription()),
                        'isTaxablePro' => 0,
                        'Taxable' => $taxAmount || (isset($taxPercent[''][0]) ?
                                                    true : false ) ? 1 : 0, // "" index for shipping tax
                        'discountAmt' => $order->getBaseShippingDiscountAmount(),
                        'AmountTotal' => $order->getBaseShippingAmount(),
                        'Type' => 'Service',
                        'TrackQtyOnHand' => 'false',
                        'ItemId' => '' // '' for shipping itemid
                    ];
                    array_push($items, $itemData);
                }

                $customerData = $this->quickBooksHelperData->getCustomerDetailForQuickbooks($order);
                $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
                $salesReceiptData = [
                    'items' => $items,
                    'customerData' => $customerData,
                    'discount_on_order' => $order->getBaseDiscountAmount(),
                    'tax_percent' => $taxPercent,
                    'paymentMethod' => $paymentMethod ? $paymentMethod : __('Magento Store Payment'),
                    'docNumber' => 'order-'.$order->getIncrementId()
                ];
                $salesReceipt = $this->orderMapFactory->create()->getCollection()
                                        ->addFieldToFilter('quickbook_sales_doc_number', $salesReceiptData['docNumber'])
                                        ->setPageSize(1)->getFirstItem();
                if (!$salesReceipt->getEntityId()) {
                    $result = $this->quickBooksHelper->createSalesReceipt($salesReceiptData, $accountId);
                    if ($result['error'] == 0) {
                        $salesReceipt = $result['salesReceiptData'];
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
                    } else {
                        $result['msg'] = $result['msg'].__(" Order Id - "). $order->getIncrementId();
                    }
                } else {
                    $result['error'] = 1;
                    $result['msg'] = 'Order #'.$order->getIncrementId().__(' already mapped.');
                }
            } else {
                $result = ['error' => 1, 'msg' => __('Currently module is disabled.')];
            }
            $this->getResponse()->representJson($this->jsonHelperData->jsonEncode($result));
        } else {
            $data = $this->getRequest()->getParams();
            $total = (int) $data['count']+1 - (int) $data['skip'];
            $msg = '<div class="wk-mu-success wk-mu-box">'.__('Total ')
                        .$total.__(' Order(s) Exported to Quickbooks.').'</div>';
            $msg .= '<div class="wk-mu-note wk-mu-box">'.__('Finished Execution.').'</div>';
            $result['msg'] = $msg;
            $this->getResponse()->representJson($this->jsonHelperData->jsonEncode($result));
        }
    }

    /**
     * Check order import permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MultiQuickbooksConnect::order_import');
    }
}
