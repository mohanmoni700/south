<?php

namespace Alfakher\SalesApprove\Controller\Adminhtml\Order;

/**
 * @author af_bv_op
 */
use Magento\Sales\Model\Order;

class Approve extends \Magento\Backend\App\Action
{
    /**
     * order status
     */
    const SALES_APPROVED = 'processing';
    # const SALES_APPROVED = 'sales_approved';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->_orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * Update order status
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $path = 'sales/order/index';
        $pathParams = [];
        $post = (array) $this->getRequest()->getParams();
        if (isset($post['order_id']) && $post['order_id']) {
            try {
                $order = $this->_orderRepository->get($post['order_id']);
                $order->setState(Order::STATE_PROCESSING)->setStatus(self::SALES_APPROVED);
                $comment = $order->addStatusHistoryComment("Sales Approved");
                $this->_orderRepository->save($order);

                $this->messageManager->addSuccessMessage(__('Order has been approved'));
                $path = 'sales/order/view';
                $pathParams = ['order_id' => $post['order_id']];
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if (!empty($message)) {
                    $this->messageManager->addErrorMessage($message);
                }
            }
        }
        return $this->resultRedirectFactory->create()->setPath($path, $pathParams);
    }
}
