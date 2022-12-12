<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Payment;

class Form extends \Magento\Framework\App\Action\Action
{
    protected $checkoutSession;

    /**
     *
     * @param \Magento\Framework\App\Action\Context     $context
     * @param \\Magento\Checkout\Model\Session          $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     *
     * @return void
     */
    public function execute()
    {
        $paymentMethod = $this->checkoutSession->getQuote()->getPayment()->getMethod();
        if ($paymentMethod == 'vrpayecommerce_easycredit') {
            $this->_redirect('vrpayecommerce/payment/easycredit', ['_secure' => true]);
        } else {
            $this->_redirect('vrpayecommerce/payment', ['_secure' => true]);
        }
    }
}
