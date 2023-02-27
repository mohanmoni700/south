<?php

namespace Corra\Spreedly\Plugin\Model\Quote\Payment;

use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Corra\Spreedly\Model\Ui\ConfigProvider;

class ToOrderPaymentPlugin
{

    /**
     * Plugin method that converts CcaResponse extension attribute from Quote Payment to Order Payment model
     *
     * @param ToOrderPayment $subject
     * @param OrderPaymentInterface $result
     * @param Payment $quotePayment
     * @param array $data
     *
     * @return OrderPaymentInterface
     */
    public function afterConvert(
        ToOrderPayment $subject,
        OrderPaymentInterface $result,
        Payment $quotePayment,
        $data = []
    ) {
        //Unsetting the additional information after convert (quote_payment: additional_data)
        foreach (ConfigProvider::ADDITIONAL_DATA as $additionalInformationKey) {
            $quotePayment->unsAdditionalInformation($additionalInformationKey);
        }
        return $result;
    }
}
