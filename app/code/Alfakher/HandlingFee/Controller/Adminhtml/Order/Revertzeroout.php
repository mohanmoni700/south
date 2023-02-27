<?php

namespace Alfakher\HandlingFee\Controller\Adminhtml\Order;

use Magento\Sales\Model\Order;

class Revertzeroout extends \Magento\Backend\App\Action
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
     * Revert orders zero out
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

                /* bv_op; reverting the shipping fee; Start */
                if ($order->getOriginalShippingFee() > 0) {
                    $order->setShippingAmount($order->getOriginalShippingFee());
                    $order->setBaseShippingAmount($order->getOriginalBaseShippingAmount());
                    $order->setShippingInclTax($order->getOriginalShippingInclTax());
                    $order->setBaseShippingInclTax($order->getOriginalBaseShippingInclTax());

                    $order->setOriginalShippingFee(0);
                    $order->setOriginalBaseShippingAmount(0);
                    $order->setOriginalShippingInclTax(0);
                    $order->setOriginalBaseShippingInclTax(0);
                }
                /* bv_op; reverting the shipping fee; End */

                /* bv_op; reverting subtotal values; Start */
                if ($order->getOriginalSubtotal() > 0) {
                    $order->setSubtotal($order->getOriginalSubtotal());
                    $order->setSubtotalInclTax($order->getOriginalSubtotalInclTax());
                    $order->setBaseSubtotal($order->getOriginalBaseSubtotal());
                    $order->setBaseSubtotalInclTax($order->getOriginalBaseSubtotalInclTax());

                    $order->setOriginalSubtotal(0);
                    $order->setOriginalSubtotalInclTax(0);
                    $order->setOriginalBaseSubtotal(0);
                    $order->setOriginalBaseSubtotalInclTax(0);
                }
                /* bv_op; reverting subtotal values; End */

                /* bv_op; reverting tax values; Start */
                if ($order->getOriginalTaxAmount() > 0) {
                    $order->setTaxAmount($order->getOriginalTaxAmount());
                    $order->setBaseTaxAmount($order->getOriginalBaseTaxAmount());
                    $order->setSalesTax($order->getOriginalSalesTax());
                    $order->setExciseTax($order->getOriginalExciseTax());

                    $order->setOriginalTaxAmount(0);
                    $order->setOriginalBaseTaxAmount(0);
                    $order->setOriginalSalesTax(0);
                    $order->setOriginalExciseTax(0);
                }
                /* bv_op; reverting tax values; End */

                /* bv_op; reverting discount; Start */
                if ($order->getOriginalDiscountAmount() < 0) {
                    $order->setDiscountAmount($order->getOriginalDiscountAmount());
                    $order->setBaseDiscountAmount($order->getOriginalBaseDiscountAmount());

                    $order->setOriginalDiscountAmount(0);
                    $order->setOriginalBaseDiscountAmount(0);
                }
                /* bv_op; reverting discount; End */

                # setting custom discounts to zero
                $order->setTotalShippingFeeDiscount(0);
                $order->setTotalSubtotalDiscount(0);

                # setting reverting grand totals
                $order->setBaseGrandTotal($order->getBaseShippingAmount()
                    + $order->getBaseSubtotal() + $order->getBaseTaxAmount()
                    + $order->getBaseDiscountAmount());
                $order->setGrandTotal($order->getShippingAmount() + $order->getSubtotal()
                    + $order->getTaxAmount() + $order->getDiscountAmount());

                /*af_bv_op; add order comment; Start */
                $adminUser = $this->getAdminDetail();
                $order->addStatusHistoryComment("Order Zero Out Reverted by -> \"" . $adminUser->getUsername() . "\"");
                /*af_bv_op; add order comment; End */

                $this->_orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('Order Zero Out Reverted successfully'));
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
