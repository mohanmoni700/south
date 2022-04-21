<?php

namespace Alfakher\HandlingFee\Controller\Adminhtml\Order;

/**
 * @author af_bv_op
 */
use Magento\Sales\Model\Order;

class Updateshippingfee extends \Magento\Backend\App\Action
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
     * Update order shipping fee
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

                if ($post['type'] == 'percent') {
                    $shippingAmount = $order->getShippingAmount();
                    $discountAmount = $shippingAmount * ($post['amount'] / 100);
                } else {
                    $discountAmount = $post['amount'];
                }

                $order->setShippingAmount($order->getShippingAmount() - $discountAmount);
                $order->setBaseShippingAmount($order->getBaseShippingAmount() - $discountAmount);
                $order->setShippingInclTax($order->getShippingInclTax() - $discountAmount);
                $order->setBaseShippingInclTax($order->getBaseShippingInclTax() - $discountAmount);
                $order->setBaseGrandTotal($order->getBaseGrandTotal() - $discountAmount);
                $order->setGrandTotal($order->getGrandTotal() - $discountAmount);

                $this->_orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('Shipping fee updated successfully'));
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
