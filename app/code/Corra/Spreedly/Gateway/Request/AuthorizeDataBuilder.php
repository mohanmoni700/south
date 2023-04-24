<?php
/**
 * @author  CORRA
 */

namespace Corra\Spreedly\Gateway\Request;

use Corra\Spreedly\Gateway\Config\Config;
use Corra\Spreedly\Gateway\Helper\SubjectReader;
use Corra\Spreedly\Model\TokenProvider;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class AuthorizeDataBuilder extends AbstractDataBuilder
{
    /**
     * Root element
     */
    private const TRANSACTION_ROOT_ELEMENT = "transaction";

    /**
     * Sub Element of Root - Credit Card Element
     */
    private const CREDIT_CARD_ELEMENT = 'credit_card';

    /**
     * The token of the payment method to use
     */
    private const PAYMENT_METHOD_TOKEN = "payment_method_token";

    /**
     * The amount to request, as an integer. E.g., 1000 for $10.00.
     */
    private const AMOUNT = "amount";

    /**
     * The currency of the funds, e.g., USD for US dollars.
     */
    private const CURRENCY_CODE = "currency_code";

    /**
     * The first name of the cardholder.
     */
    private const FIRST_NAME = 'first_name';

    /**
     * The last name of the cardholder
     */
    private const LAST_NAME = 'last_name';

    /**
     * Magento Order ID
     */
    private const ORDER_ID = 'order_id';

    /**
     * The verification value (CVV/CVC) of the card
     */
    private const CVV = "verification_value";

    /**
     * The expiration month of the card
     */
    private const PAYMENT_INFO_EXP_MONTH = 'month';

    /**
     * The expiration year of the card
     */
    private const PAYMENT_INFO_EXP_YEAR = 'year';

    /**
     * The full credit card number
     */
    private const FULL_CARD_NUMBER = "number";

    /**
     * Need to send as true if card is being saved
     */
    private const RETAIN_ON_SUCCESS = "retain_on_success";
    /**
     * Ip address of the customer
     */
    private const CUSTOMER_IP = "ip";

    /**
     * Email address of the customer
     */
    private const CUSTOMER_EMAIL = "email";

    /**
     * @var array
     */
    protected $additionalInformationList = [
        'payment_method_token',
        'cc_number',
        'cc_cid',
        'cc_exp_month',
        'cc_exp_year'
    ];
    /** @var RemoteAddress  */
    private RemoteAddress $remoteAddress;

    /**
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param TokenProvider $tokenProvider
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config,
        TokenProvider $tokenProvider,
        RemoteAddress $remoteAddress
    )
    {
        parent::__construct($subjectReader, $config, $tokenProvider);
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @inheritdoc
     */
    public function getUrl(array $buildSubject)
    {
        return $this->config->getServiceUrl() . 'gateways/' . $this->getGatewayToken() . '/authorize.json';
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
        $vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();
        $vaultGatewayToken = $vaultPaymentToken ? $vaultPaymentToken->getGatewayToken() : '';

        try {
            $amount = $this->subjectReader->readAmount($buildSubject);
        } catch (\InvalidArgumentException $e) {
            // getting a full authorized amount
            $amount = $payment->getBaseAmountAuthorized();
        }

        $cc_number = !empty($payment->getAdditionalInformation('cc_number')) ?
            $payment->getAdditionalInformation('cc_number') : $paymentDO->getPayment()->getCcNumber();
        $cc_cvv = !empty($payment->getAdditionalInformation('cc_cid')) ?
            $payment->getAdditionalInformation('cc_cid') : $paymentDO->getPayment()->getCcCid();
        $payment_method_token = !empty($payment->getAdditionalInformation('payment_method_token'))
            ? $payment->getAdditionalInformation('payment_method_token')
            : $vaultGatewayToken;
        $payment_token_enabled = !empty($payment->getAdditionalInformation('is_active_payment_token_enabler')) ?
            $payment->getAdditionalInformation('is_active_payment_token_enabler') : false;

        if (!empty($cc_number)) {
            $result = [
                self::TRANSACTION_ROOT_ELEMENT => [
                    self::RETAIN_ON_SUCCESS => $payment_token_enabled,
                    self::CREDIT_CARD_ELEMENT => [
                        self::FIRST_NAME => $billingAddress->getFirstname(),
                        self::LAST_NAME => $billingAddress->getLastname(),
                        self::FULL_CARD_NUMBER => $cc_number,
                        self::CVV => $cc_cvv,
                        self::PAYMENT_INFO_EXP_MONTH => $payment->getAdditionalInformation('cc_exp_month'),
                        self::PAYMENT_INFO_EXP_YEAR => $payment->getAdditionalInformation('cc_exp_year')
                    ],
                    self::AMOUNT => $this->formatAmount($amount),
                    self::CURRENCY_CODE => $order->getCurrencyCode(),
                    self::ORDER_ID => $order->getOrderIncrementId()
                ]
            ];
        } else {
            $result = [
                self::TRANSACTION_ROOT_ELEMENT => [
                    self::RETAIN_ON_SUCCESS => $payment_token_enabled,
                    self::PAYMENT_METHOD_TOKEN => $payment_method_token,
                    self::AMOUNT => $this->formatAmount($amount),
                    self::CURRENCY_CODE => $order->getCurrencyCode(),
                    self::ORDER_ID => $order->getOrderIncrementId(),
                    self::CUSTOMER_IP => $this->remoteAddress->getRemoteAddress() ?? '127.0.0.1',
                    self::CUSTOMER_EMAIL => $billingAddress->getEmail() ?? null
                ]
            ];
        }
        //Unsetting the additional information after request builder (sales_order_payment: additional_data)
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            $payment->unsAdditionalInformation($additionalInformationKey);
        }

        return $result;
    }
}
