<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Request;

class PaymentMethodTokenDataBuilder extends AbstractDataBuilder
{
    /**
     * Root element
     */
    private const PAYMENT_METHOD_ROOT_ELEMENT = "payment_method";

    /**
     * Sub Element of Root - Credit Card Element
     */
    private const CREDIT_CARD_ELEMENT = 'credit_card';

    /**
     * Sub Element of Root -  Metadata Element
     */
    private const METADATA_ELEMENT = 'metadata';

    /**
     * The first name of the cardholder.
     */
    private const FIRST_NAME = 'first_name';

    /**
     * The last name of the cardholder
     */
    private const LAST_NAME = 'last_name';

    /**
     * The expiration month of the card
     */
    private const PAYMENT_INFO_EXP_MONTH = 'month';

    /**
     * The expiration year of the card
     */
    private const PAYMENT_INFO_EXP_YEAR = 'year';

    /**
     * The company name associated with the credit card
     */
    private const COMPANY = 'company';

    /**
     * The verification value (CVV/CVC) of the card
     */
    private const CVV = "verification_value";

    /**
     * The full credit card number
     */
    private const FULL_CARD_NUMBER = "number";

    /**
     * The customer email address
     */
    private const EMAIL = 'email';

    /**
     * @inheritdoc
     */
    public function getUrl(array $buildSubject)
    {
        return $this->config->getServiceUrl() . 'payment_methods.json';
    }

    /**
     * @inheritdoc
     */
    public function getMethod(array $buildSubject)
    {
        return 'POST';
    }

    /**
     * @inheritdoc
     */
    public function getBody(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        $billingAddress = $order->getBillingAddress();
        $company = !empty($billingAddress->getCompany())?$billingAddress->getCompany():'';
        return [
            self::PAYMENT_METHOD_ROOT_ELEMENT => [
                self::CREDIT_CARD_ELEMENT => [
                    self::FIRST_NAME => $billingAddress->getFirstname(),
                    self::LAST_NAME => $billingAddress->getLastname(),
                    self::CVV => $paymentDO->getPayment()->getCcCid(),
                    self::FULL_CARD_NUMBER => $paymentDO->getPayment()->getCcNumber(),
                    self::PAYMENT_INFO_EXP_MONTH => $payment->getAdditionalInformation('cc_exp_month'),
                    self::PAYMENT_INFO_EXP_YEAR => $payment->getAdditionalInformation('cc_exp_year'),
                    self::COMPANY => $company,
                    'address1' => $billingAddress->getStreetLine1(),
                    'address2' => $billingAddress->getStreetLine2(),
                    'city' => $billingAddress->getCity(),
                    'state' => $billingAddress->getRegionCode(),
                    'country' => $billingAddress->getCountryId(),
                    'zip' => $billingAddress->getPostcode(),
                    'phone_number' => $billingAddress->getTelephone(),
                    'shipping_address1' => $order->getShippingAddress()->getStreetLine1(),
                    'shipping_address2' => $order->getShippingAddress()->getStreetLine2(),
                    'shipping_city' => $order->getShippingAddress()->getCity(),
                    'shipping_state' => $order->getShippingAddress()->getRegionCode(),
                    'shipping_country' => $order->getShippingAddress()->getCountryId(),
                    'shipping_zip' => $order->getShippingAddress()->getPostcode(),
                    'shipping_phone_number' => $order->getShippingAddress()->getTelephone()
                    ],
                'retained' => true,
                self::EMAIL => $billingAddress->getEmail(),
                self::METADATA_ELEMENT => [
                    'key' => "string value",
                    'another_key' => 123,
                    'final_key' =>true
                ]
            ]
        ];
    }
}
