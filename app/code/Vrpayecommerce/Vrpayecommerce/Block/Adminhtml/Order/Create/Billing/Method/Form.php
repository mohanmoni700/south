<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Block\Adminhtml\Order\Create\Billing\Method;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method\Form
{
    protected $payment;
    protected $information;
    protected $paymentMethod;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context         $context
     * @param \Magento\Payment\Helper\Data                             $paymentHelper
     * @param \Magento\Payment\Model\Checks\SpecificationFactory       $methodSpecificationFactory
     * @param \Magento\Backend\Model\Session\Quote                     $sessionQuote
     * @param \Vrpayecommerce\Vrpayecommerce\Controller\Payment        $payment
     * @param \Vrpayecommerce\Vrpayecommerce\Model\Payment\Information $information
     * @param array                                                    $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Vrpayecommerce\Vrpayecommerce\Controller\Payment $payment,
        \Vrpayecommerce\Vrpayecommerce\Model\Payment\Information $information,
        array $data = []
    ) {
        $this->payment = $payment;
        $this->information = $information;
        parent::__construct($context, $paymentHelper, $methodSpecificationFactory, $sessionQuote, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Vrpayecommerce_Vrpayecommerce::order/create/billing/method/form.phtml');

        return $this;
    }

    /**
     * get a customer id
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_sessionQuote->getCustomerId();
    }

    /**
     * check if a payment method is VR pay ecommerce
     * @param  string  $paymentMethod
     * @return boolean
     */
    public function isVrpayecommerceMethod($paymentMethod)
    {
        return $this->payment->isVrpayecommerceMethod($paymentMethod);
    }

    /**
     * check if the VR pay eCommerce payment methods are  payment methods recurring
     * @param  string  $paymentMethod
     * @return boolean|string
     */
    public function isBackendPaymentMethod($paymentMethod)
    {
        $isVrpayecommerceMethod = $this->payment->isVrpayecommerceMethod($paymentMethod);
        if ($isVrpayecommerceMethod) {
            $this->paymentMethod = $this->payment->createPaymentMethodObjectByPaymentMethod($paymentMethod);
            return ($this->paymentMethod->isRecurringPayment() && $this->paymentMethod->isRecurring());
        }
        return true;
    }

    /**
     * get payments information
     * @param  string $paymentMethod
     * @return array
     */
    public function getPaymentInformation($paymentMethod)
    {
        $this->paymentMethod = $this->payment->createPaymentMethodObjectByPaymentMethod($paymentMethod);
        $informationParameters = [];
        $informationParameters['customerId'] = $this->getCustomerId();
        $informationParameters['serverMode'] = $this->paymentMethod->getServerMode();
        $informationParameters['channelId'] = $this->paymentMethod->getChannelId();
        $informationParameters['paymentGroup'] =  $this->paymentMethod->getPaymentGroup();

        return $this->information->getPaymentInformation($informationParameters);
    }

    /**
     * get payments information selected
     * @param  string $registrationId
     * @return array
     */
    public function getPaymentInformationSelection($registrationId)
    {
        return $this->information->getRegistrationByRegistrationId($registrationId);
    }

    /**
     * get payments method recurring accounts details
     * @param  [type] $paymentInformation
     * @return [type]
     */
    public function getAccountDetail($paymentInformation)
    {
        switch ($paymentInformation['payment_group']) {
            case 'CC':
                return __('FRONTEND_MC_ENDING').": ".$paymentInformation['last_4digits']."; ".
                    __('FRONTEND_MC_VALIDITY').": ".
                        $paymentInformation['expiry_month']."/".substr($paymentInformation['expiry_year'], -2);
            case 'DD':
                return __('FRONTEND_MC_ACCOUNT').": **** ".$paymentInformation['last_4digits'];
            case 'PAYPAL':
                return __('FRONTEND_MC_EMAIL').": ".$paymentInformation['email'];
        }
    }

    /**
     * get a payment method
     * @return string
     */
    public function getPaymentMethodSession()
    {
        return $this->_sessionQuote->getVrpayecommercePaymentMethod();
    }

    /**
     * get a registration Id
     * @return string
     */
    public function getRegistrationIdSession()
    {
        return $this->_sessionQuote->getVrpayecommerceRegistrationId();
    }
}
