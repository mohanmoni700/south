<?php

namespace Alfakher\HandlingFee\Controller\Adminhtml\Order;

use Magento\Sales\Model\Order;

class Updatesubtotal extends \Magento\Backend\App\Action
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
     * Update order subtotal
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
                    $subtotal = $order->getSubtotal();
                    $discountAmount = $subtotal * ($post['amount'] / 100);
                    $errMsg = "Maximum discount on subtotal can’t be more than $";
                    if ($discountAmount > $order->getSubtotal() && $discountAmount != 0) {
                        $this->messageManager->addErrorMessage(__($errMsg . $order->getSubtotal()));
                        $result = $this->_resultJsonFactory->create();
                        $result->setData(['status' => false]);
                        return $result;
                    }

                    /*af_bv_op; Start*/
                    if ($order->getOriginalSubtotal() <= 0) {
                        $order->setOriginalSubtotal($order->getSubtotal());
                        $order->setOriginalSubtotalInclTax($order->getSubtotalInclTax());
                        $order->setOriginalBaseSubtotal($order->getBaseSubtotal());
                        $order->setOriginalBaseSubtotalInclTax($order->getBaseSubtotalInclTax());
                    }
                    $order->setTotalSubtotalDiscount($order->getTotalSubtotalDiscount() + $discountAmount);
                    /*af_bv_op; End*/

                    $order->setSubtotal($order->getSubtotal() - $discountAmount);
                    $order->setSubtotalInclTax($order->getSubtotalInclTax() - $discountAmount);
                    $order->setBaseSubtotal($order->getBaseSubtotal() - $discountAmount);
                    $order->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax() - $discountAmount);
                    $order->setBaseGrandTotal($order->getBaseGrandTotal() - $discountAmount);
                    $order->setGrandTotal($order->getGrandTotal() - $discountAmount);

                } else {
                    $subtotal = $order->getSubtotal();
                    $discountAmount = $post['amount'];
                    $errMsg = "Maximum discount on subtotal can’t be more than $";
                    if ($discountAmount > $order->getSubtotal() && $discountAmount != 0) {
                        $this->messageManager->addErrorMessage(__($errMsg . $order->getSubtotal()));
                        $result = $this->_resultJsonFactory->create();
                        $result->setData(['status' => false]);
                        return $result;
                    }

                    if ($discountAmount > 0) {
                        /*af_bv_op; Start*/
                        if ($order->getOriginalSubtotal() <= 0) {
                            $order->setOriginalSubtotal($order->getSubtotal());
                            $order->setOriginalSubtotalInclTax($order->getSubtotalInclTax());
                            $order->setOriginalBaseSubtotal($order->getBaseSubtotal());
                            $order->setOriginalBaseSubtotalInclTax($order->getBaseSubtotalInclTax());
                        }
                        /*af_bv_op; End*/

                        $order->setSubtotal($order->getSubtotal() - $discountAmount);
                        $order->setSubtotalInclTax($order->getSubtotalInclTax() - $discountAmount);
                        $order->setBaseSubtotal($order->getBaseSubtotal() - $discountAmount);
                        $order->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax() - $discountAmount);
                        $order->setBaseGrandTotal($order->getBaseGrandTotal() - $discountAmount);
                        $order->setGrandTotal($order->getGrandTotal() - $discountAmount);

                        /*af_bv_op; Start*/
                        $order->setTotalSubtotalDiscount($order->getTotalSubtotalDiscount() + $discountAmount);
                        /*af_bv_op; End*/
                    } elseif ($order->getOriginalSubtotal() > 0) {

                        $order->setSubtotal($order->getOriginalSubtotal());
                        $order->setSubtotalInclTax($order->getOriginalSubtotalInclTax());
                        $order->setBaseSubtotal($order->getOriginalBaseSubtotal());
                        $order->setBaseSubtotalInclTax($order->getOriginalBaseSubtotalInclTax());
                        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getTotalSubtotalDiscount());
                        $order->setGrandTotal($order->getGrandTotal() + $order->getTotalSubtotalDiscount());

                        $order->setOriginalSubtotal(0);
                        $order->setOriginalSubtotalInclTax(0);
                        $order->setOriginalBaseSubtotal(0);
                        $order->setOriginalBaseSubtotalInclTax(0);
                        $order->setTotalSubtotalDiscount(0);
                    } else {
                        $this->messageManager->addErrorMessage(__("Please enter a valid amount greater than $0."));
                        $result = $this->_resultJsonFactory->create();
                        $result->setData(['status' => false]);
                        return $result;
                    }
                }

                /*af_bv_op; add order comment; Start */
                $adminUser = $this->getAdminDetail();
                $order->addStatusHistoryComment("Discount applied on subtotal by -> \""
                        . $adminUser->getUsername() . "\" : " . $post['amount']
                        . "(" . $post['type'] . ")");
                /*af_bv_op; add order comment; End */

                $this->_orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('Subtotal updated successfully'));
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
