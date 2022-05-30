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
     * Try to load valid collection of ordered items
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadOrderItems($orderId)
    {
        /** @var $rmaHelper \Magento\Rma\Helper\Data */
        $rmaHelper = $this->_objectManager->get(\Magento\Rma\Helper\Data::class);

        $resultRedirect = $this->_objectManager->create(\Magento\Framework\Controller\Result\Redirect::class);

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
        $resultRedirect = $this->_objectManager->create(\Magento\Framework\Controller\Result\Redirect::class);
        if (empty($orderId)) {
            return $resultRedirect->setPath('sales/order/history');
        }
        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);

        if (!$this->_canViewOrder($order)) {
            return $resultRedirect->setPath('sales/order/history');
        }

        $this->_coreRegistry->register('current_order', $order);

        if (!$this->_loadOrderItems($orderId)) {
            return;
        }
        $resultPage = $this->_objectManager->create(\Magento\Framework\View\Result\Page::class);

        $resultPage->initLayout();
        $resultPage->getLayout();
        $resultPage->getConfig()->getTitle()->prepend((__('Create New Return All')));

        if ($block = $this->_view->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($resultredirection->getRefererUrl());
        }

        return $resultPage;
    }
}
