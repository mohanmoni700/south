<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Request;

class AuthorizeTokenDataBuilder extends AbstractDataBuilder
{
    /**
     * Root element
     */
    private const TRANSACTION_ROOT_ELEMENT = "transaction";

    /**
     * The token of the payment method to use
     */
    private const PAYMENT_METHOD_TOKEN ="payment_method_token";

    /**
     * The amount to request, as an integer. E.g., 1000 for $10.00.
     */
    private const AMOUNT ="amount";

    /**
     * The currency of the funds, e.g., USD for US dollars.
     */
    private const CURRENCY_CODE = "currency_code";

    /**
     * Magento Order ID
     */
    private const ORDER_ID = 'order_id';

    /**
     * @inheritdoc
     */
    public function getUrl(array $buildSubject)
    {
        return $this->config->getServiceUrl() .'gateways/'. $this->getGatewayToken().'/authorize.json';
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
        try {
            $amount = $this->subjectReader->readAmount($buildSubject);
        } catch (\InvalidArgumentException $e) {
            // seems we are doing authorization reversal, getting a full authorized amount
            $amount = $payment->getBaseAmountAuthorized();
        }
        return [
            self::TRANSACTION_ROOT_ELEMENT => [
                self::PAYMENT_METHOD_TOKEN =>$payment->getAdditionalInformation('token_data'),
                self::AMOUNT => $this->formatAmount($amount),
                self::CURRENCY_CODE =>$order->getCurrencyCode(),
                self::ORDER_ID => $order->getOrderIncrementId()
            ]
        ];
    }
}
