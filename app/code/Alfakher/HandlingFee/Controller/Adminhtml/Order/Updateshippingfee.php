<?php

namespace Alfakher\HandlingFee\Controller\Adminhtml\Order;

use Magento\Sales\Model\Order;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Updateshippingfee extends Action
{
    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param JsonFactory $resultJsonFactory
     * @param Session $authSession
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        Session $authSession,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->authSession = $authSession;
        $this->priceCurrency = $priceCurrency;

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
                $formattedShipping = $this->priceCurrency
                    ->format($order->getShippingAmount(), false, 2, $order->getStoreId());

                if ($post['type'] == 'percent') {
                    $shippingAmount = $order->getShippingAmount();
                    $discountAmount = $shippingAmount * ($post['amount'] / 100);
                    $errMsg = "Maximum discount on shipping fee can’t be more than ";
                    if ($discountAmount > $order->getShippingAmount() && $discountAmount != 0) {
                        $this->messageManager->addErrorMessage(__($errMsg . $formattedShipping));
                        $result = $this->_resultJsonFactory->create();
                        $result->setData(['status' => false]);
                        return $result;
                    }

                    /*af_bv_op; Start*/
                    if ($order->getOriginalShippingFee() <= 0) {
                        $order->setOriginalShippingFee($order->getShippingAmount());
                        $order->setOriginalBaseShippingAmount($order->getBaseShippingAmount());
                        $order->setOriginalShippingInclTax($order->getShippingInclTax());
                        $order->setOriginalBaseShippingInclTax($order->getBaseShippingInclTax());
                    }
                    $order->setTotalShippingFeeDiscount($order->getTotalShippingFeeDiscount() + $discountAmount);
                    /*af_bv_op; End*/

                    $order->setShippingAmount($order->getShippingAmount() - $discountAmount);
                    $order->setBaseShippingAmount($order->getBaseShippingAmount() - $discountAmount);
                    $order->setShippingInclTax($order->getShippingInclTax() - $discountAmount);
                    $order->setBaseShippingInclTax($order->getBaseShippingInclTax() - $discountAmount);
                    $order->setBaseGrandTotal($order->getBaseGrandTotal() - $discountAmount);
                    $order->setGrandTotal($order->getGrandTotal() - $discountAmount);

                } else {
                    $shippingAmount = $order->getShippingAmount();
                    $discountAmount = $post['amount'];
                    $errMsg = "Maximum discount on shipping fee can’t be more than ";
                    if ($discountAmount > $order->getShippingAmount() && $discountAmount != 0) {
                        $this->messageManager->addErrorMessage(__($errMsg . $formattedShipping));
                        $result = $this->_resultJsonFactory->create();
                        $result->setData(['status' => false]);
                        return $result;
                    }

                    if ($discountAmount > 0) {
                        if ($order->getOriginalShippingFee() <= 0) {
                            $order->setOriginalShippingFee($order->getShippingAmount());
                            $order->setOriginalBaseShippingAmount($order->getBaseShippingAmount());
                            $order->setOriginalShippingInclTax($order->getShippingInclTax());
                            $order->setOriginalBaseShippingInclTax($order->getBaseShippingInclTax());
                        }

                        $order->setShippingAmount($order->getShippingAmount() - $discountAmount);
                        $order->setBaseShippingAmount($order->getBaseShippingAmount() - $discountAmount);
                        $order->setShippingInclTax($order->getShippingInclTax() - $discountAmount);
                        $order->setBaseShippingInclTax($order->getBaseShippingInclTax() - $discountAmount);
                        $order->setBaseGrandTotal($order->getBaseGrandTotal() - $discountAmount);
                        $order->setGrandTotal($order->getGrandTotal() - $discountAmount);

                        $order->setTotalShippingFeeDiscount($order->getTotalShippingFeeDiscount() + $discountAmount);
                    } elseif ($order->getOriginalShippingFee() > 0) {
                        $order->setShippingAmount($order->getOriginalShippingFee());
                        $order->setBaseShippingAmount($order->getOriginalBaseShippingAmount());
                        $order->setShippingInclTax($order->getOriginalShippingInclTax());
                        $order->setBaseShippingInclTax($order->getOriginalBaseShippingInclTax());
                        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getTotalShippingFeeDiscount());
                        $order->setGrandTotal($order->getGrandTotal() + $order->getTotalShippingFeeDiscount());

                        $order->setOriginalShippingFee(0);
                        $order->setOriginalBaseShippingAmount(0);
                        $order->setOriginalShippingInclTax(0);
                        $order->setOriginalBaseShippingInclTax(0);
                        $order->setTotalShippingFeeDiscount(0);
                    } else {
                        $currencySymbol = $this->priceCurrency->getCurrencySymbol($order->getStoreId());
                        $invalidAmount = "Please enter a valid amount greater than ".$currencySymbol."0.";
                        $this->messageManager->addErrorMessage(__($invalidAmount));
                        $result = $this->_resultJsonFactory->create();
                        $result->setData(['status' => false]);
                        return $result;
                    }
                }

                /*af_bv_op; add order comment; Start */
                $adminUser = $this->getAdminDetail();
                $order->addStatusHistoryComment("Discount applied on shipping fee by -> \""
                    . $adminUser->getUsername() . "\" : "
                    . $post['amount'] . "(" . $post['type'] . ")");
                /*af_bv_op; add order comment; End */

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
