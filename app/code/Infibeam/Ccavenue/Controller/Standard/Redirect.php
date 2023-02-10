<?php

namespace Infibeam\Ccavenue\Controller\Standard;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Redirect extends \Infibeam\Ccavenue\Controller\CcavenueAbstract implements CsrfAwareActionInterface {

    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }
    
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute() {
        if (!$this->getRequest()->isAjax()) {
            $this->_cancelPayment();
            $this->getResponse()->setRedirect(
                    $this->getCheckoutHelper()->getUrl('checkout')
            );
        }

        $quote = $this->getQuote();
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->getCheckoutSession()->loadCustomerQuote();
            $quote->updateCustomerData($this->getQuote()->getCustomer());
        }

        if ($this->getCustomerSession()->isLoggedIn()) {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
        } else {
            $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        }

        $quote->save();

        //set before payment status
        $paymentMethod = $this->getPaymentMethod();
        $order = $this->getOrder();
        $order->setStatus($paymentMethod->getConfigData('new_order_status'));
        $order->setState(\Magento\Sales\Model\Order::STATE_NEW);
        $order->addStatusHistoryComment('Redirected to ccavenue', false);
        $order->save();

        $params = [];
        $params["fields"] = $paymentMethod->buildCheckoutRequest();
        $params["url"] = $paymentMethod->getCgiUrl();

        $this->_logger->info('Transaction URL : '.$params["url"]);
        $this->_logger->info('Redirected to ccavenue.');

        return $this->resultJsonFactory->create()->setData($params);
    }

}
