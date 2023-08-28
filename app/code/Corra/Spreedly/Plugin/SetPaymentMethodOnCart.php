<?php
/**
 * @author  CORRA
 */

declare(strict_types=1);

namespace Corra\Spreedly\Plugin;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Model\Quote;
use Corra\Spreedly\Model\Ui\ConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Set additionalInformation on payment for Spreedly method
 */
class SetPaymentMethodOnCart
{
    private const PATH_ADDITIONAL_DATA = 'additional_data';
    /**
     * Set Spreedly Checkout Token and Other items
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject
     * @param mixed $result
     * @param Quote $cart
     * @param array $paymentData
     * @return void
     * @throws GraphQlInputException
     */
    public function afterExecute(
        \Magento\QuoteGraphQl\Model\Cart\SetPaymentMethodOnCart $subject,
        $result,
        Quote $cart,
        array $paymentData
    ): void {
        $paymentMethod = $cart->getPayment()->getMethod();
        $payment = $cart->getPayment();

        if (($paymentMethod === ConfigProvider::CODE)
            && (array_key_exists('cc_number', $paymentData[self::PATH_ADDITIONAL_DATA]) ||
                array_key_exists('payment_method_token', $paymentData[self::PATH_ADDITIONAL_DATA])
            )
        ) {
            // This is not good practise..will do this change based on FE how we are implementing payment method
            foreach (ConfigProvider::ADDITIONAL_DATA as $additionalInformationKey) {
                if (isset($paymentData[self::PATH_ADDITIONAL_DATA][$additionalInformationKey]) &&
                    !empty($paymentData[self::PATH_ADDITIONAL_DATA][$additionalInformationKey])) {
                    $payment->setAdditionalInformation(
                        $additionalInformationKey,
                        $paymentData[self::PATH_ADDITIONAL_DATA][$additionalInformationKey]
                    );
                }
            }
        }

        if ($paymentMethod === ConfigProvider::CC_VAULT_CODE &&
            isset($paymentData[ConfigProvider::CC_VAULT_CODE][PaymentTokenInterface::PUBLIC_HASH]))
        {
            $publicHash = $paymentData[ConfigProvider::CC_VAULT_CODE][PaymentTokenInterface::PUBLIC_HASH];

            $payment->setAdditionalInformation([
                PaymentTokenInterface::PUBLIC_HASH => $publicHash,
                PaymentTokenInterface::CUSTOMER_ID => $cart->getCustomerId()
            ]);
        }

        $payment->save();
    }
}
