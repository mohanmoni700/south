<?php

namespace Alfakher\HandlingFee\Controller\Adminhtml\Order;

use Magento\Sales\Model\Order;

class Zeroout extends \Magento\Backend\App\Action
{

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->authSession = $authSession;

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

                /* bv_op; backup the original shipping fee values; Start */
                if ($order->getOriginalShippingFee() <= 0) {
                    $order->setOriginalShippingFee($order->getShippingAmount());
                    $order->setOriginalBaseShippingAmount($order->getBaseShippingAmount());
                    $order->setOriginalShippingInclTax($order->getShippingInclTax());
                    $order->setOriginalBaseShippingInclTax($order->getBaseShippingInclTax());
                }
                /* bv_op; backup the original shipping fee values; End */

                /* bv_op; backup the original subtotal values; Start */
                if ($order->getOriginalSubtotal() <= 0) {
                    $order->setOriginalSubtotal($order->getSubtotal());
                    $order->setOriginalSubtotalInclTax($order->getSubtotalInclTax());
                    $order->setOriginalBaseSubtotal($order->getBaseSubtotal());
                    $order->setOriginalBaseSubtotalInclTax($order->getBaseSubtotalInclTax());
                }
                /* bv_op; backup the original subtotal values; End */

                /* bv_op; backup the original tax values; Start */
                if ($order->getOriginalTaxAmount() <= 0) {
                    $order->setOriginalTaxAmount($order->getTaxAmount());
                    $order->setOriginalBaseTaxAmount($order->getBaseTaxAmount());
                    $order->setOriginalSalesTax($order->getSalesTax());
                    $order->setOriginalExciseTax($order->getExciseTax());
                }
                /* bv_op; backup the original tax values; End */

                /* bv_op; backup the original discount; Start */
                if ($order->getOriginalDiscountAmount() <= 0) {
                    $order->setOriginalDiscountAmount($order->getDiscountAmount());
                    $order->setOriginalBaseDiscountAmount($order->getBaseDiscountAmount());
                }
                /* bv_op; backup the original discount; End */

                /* bv_op; setting all values to zero; Start */
                # setting custom discounts to zero
                $order->setTotalShippingFeeDiscount(0);
                $order->setTotalSubtotalDiscount(0);

                # setting subtotals to zero
                $order->setSubtotal(0);
                $order->setSubtotalInclTax(0);
                $order->setBaseSubtotal(0);
                $order->setBaseSubtotalInclTax(0);

                # setting shipping fees to zero
                $order->setShippingAmount(0);
                $order->setBaseShippingAmount(0);
                $order->setShippingInclTax(0);
                $order->setBaseShippingInclTax(0);

                # setting tax values to zero
                $order->setTaxAmount(0);
                $order->setBaseTaxAmount(0);
                $order->setSalesTax(0);
                $order->setExciseTax(0);
                /* bv_op; setting all values to zero; End */

                # setting discount to zero
                $order->setDiscountAmount(0);
                $order->setBaseDiscountAmount(0);

                # setting grand totals to zero
                $order->setBaseGrandTotal(0);
                $order->setGrandTotal(0);

                # setting handling fee to zero
                $order->setHandlingFee(0);

                /*af_bv_op; add order comment; Start */
                $adminUser = $this->getAdminDetail();
                $order->addStatusHistoryComment("Order Zero Out by -> \"" . $adminUser->getUsername() . "\"");
                /*af_bv_op; add order comment; End */

                $this->_orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('Order Zero Out successfully'));
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

    /**
     * Get admin user detail
     *
     * @return mixed
     */
    public function getAdminDetail()
    {
        return $this->authSession->getUser();
    }
}
