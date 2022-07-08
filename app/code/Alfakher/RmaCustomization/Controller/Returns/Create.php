<?php
namespace Alfakher\RmaCustomization\Controller\Returns;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Controller class create. Renders returns creation page
 *
 * @param Magento\Rma\Controller\Returns
 */
class Create extends \Magento\Rma\Controller\Returns implements HttpGetActionInterface
{
    /**
     *
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     *
     * @var \Magento\Rma\Helper\Data
     */
    protected $rmadata;
    /**
     *
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;
    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;
    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Rma\Helper\Data $rmadata
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Rma\Helper\Data $rmadata,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->rmadata = $rmadata;
        $this->redirectFactory = $redirectFactory;
        $this->orderFactory = $orderFactory;
        $this->pageFactory = $pageFactory;
        parent::__construct($context, $coreRegistry);
    }
    /**
     * Try to load valid collection of ordered items
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadOrderItems($orderId)
    {
        /** @var $rmaHelper \Magento\Rma\Helper\Data */
        $rmaHelper = $this->rmadata;

        $resultRedirect = $this->redirectFactory->create();

        if ($rmaHelper->canCreateRma($orderId)) {
            return true;
        }

        $incrementId = $this->_coreRegistry->registry('current_order')->getIncrementId();
        $message = __('We can\'t create a return transaction for order #%1.', $incrementId);
        $this->messageManager->addError($message);
        $resultRedirect->setPath('sales/order/history');
        return false;
    }

    /**
     * Customer create new return
     *
     * @return void
     */
    public function execute()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->redirectFactory->create();

        if (empty($orderId)) {
            return $resultRedirect->setPath('sales/order/history');
        }
        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create()->load($orderId);

        if (!$this->_canViewOrder($order)) {
            return $resultRedirect->setPath('sales/order/history');
        }

        $this->_coreRegistry->register('current_order', $order);

        if (!$this->_loadOrderItems($orderId)) {
            return;
        }
        $resultPage = $this->pageFactory->create();

        $resultPage->initLayout();
        $resultPage->getLayout();
        $resultPage->getConfig()->getTitle()->prepend((__('Create New Return All')));

        if ($block = $this->_view->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($resultredirection->getRefererUrl());
        }

        return $resultPage;
    }
}
