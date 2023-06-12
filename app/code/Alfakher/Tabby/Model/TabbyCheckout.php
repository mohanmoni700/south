<?php

namespace Alfakher\Tabby\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Tabby\Checkout\Model\Method\Checkout;

class TabbyCheckout extends Checkout
{
    protected $_codeTabby = 'installments';

    /**
     * @var Order
     */
    private $order;

    /**
     * @param  Order $order
     * @return void
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Returns tabby redirect URL
     *
     * @return string
     * @throws LocalizedException
     */
    public function getOrderRedirectUrl()
    {
        $requestData = [
            "lang"          => strstr($this->localeResolver->getLocale(), '_', true) == 'en' ? 'en' : 'ar',
            "merchant_code" => $this->order->getStore()->getCode() . ($this->getConfigData('local_currency') ? '_' . $this->order->getOrderCurrencyCode() : ''),
            "merchant_urls" => $this->getMerchantUrls(),
            "payment"       => $this->getSessionPaymentObject($this->order)
        ];

        $redirectUrl = $this->_urlInterface->getUrl('tabby/result/failure');

        try {
            $result = $this->_checkoutApi->createSession($this->order->getStoreId(), $requestData);

            if ($result && property_exists($result, 'status') && $result->status == 'created') {
                if (property_exists($result->configuration->available_products, $this->_codeTabby)) {
                    // register new payment id for order
                    $this->getInfoInstance()->setAdditionalInformation([
                        self::PAYMENT_ID_FIELD => $result->payment->id
                    ]);
                    $redirectUrl = $result->configuration->available_products->{$this->_codeTabby}[0]->web_url;
                } else {
                    throw new LocalizedException(__("Selected payment method not available."));
                }
            } else {
                throw new LocalizedException(__("Response not have status field or payment rejected"));
            }
        } catch (\Exception $e) {
            $this->_ddlog->log("error", "createSession exception", $e, $requestData);
            throw new LocalizedException(__("Something went wrong. Please try again later or contact support."));
        }

        return $redirectUrl;
    }

    /**
     * @ingeritdoc
     */
    public function getInfoInstance()
    {
        $instance = $this->order->getPayment();

        if (!$instance instanceof InfoInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We cannot retrieve the payment information object instance.')
            );
        }

        return $instance;
    }
}
