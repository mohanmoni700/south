<?php

namespace Alfakher\HandlingFee\Controller\Adminhtml\Order;

/**
 * @author af_bv_op
 */
use Magento\Sales\Model\Order;

class Updatefee extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * Update order status
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $status = false;
        $post = (array) $this->getRequest()->getParams();
        if (isset($post['order_id']) && $post['order_id']) {
            try {
                $order = $this->_orderRepository->get($post['order_id']);

                $originalHandlingFee = $order->getHandlingFee();
                $newHandlingFee = $post['amount'];

                if ($originalHandlingFee >= $newHandlingFee) {
                    $adjustHandlingFee = $originalHandlingFee - $newHandlingFee;
                    if ($adjustHandlingFee < 0) {
                        $adjustHandlingFee = 0;
                    }
                    $order->setHandlingFee($post['amount']);
                    $order->setGrandTotal($order->getGrandTotal() - $adjustHandlingFee);
                    $order->setBaseGrandTotal($order->getBaseGrandTotal() - $adjustHandlingFee);
                } else {
                    $adjustHandlingFee = $newHandlingFee - $originalHandlingFee;
                    if ($adjustHandlingFee < 0) {
                        $adjustHandlingFee = 0;
                    }
                    $order->setHandlingFee($post['amount']);
                    $order->setGrandTotal($order->getGrandTotal() + $adjustHandlingFee);
                    $order->setBaseGrandTotal($order->getBaseGrandTotal() + $adjustHandlingFee);
                }

                $this->_orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('Handling Fee updated successfully'));
                $status = true;
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if (!empty($message)) {
                    $this->messageManager->addErrorMessage($message);
                }
            }
        }

        $result = $this->_resultJsonFactory->create();
        $result->setData(['status' => $status]);
        return $result;
    }
}
