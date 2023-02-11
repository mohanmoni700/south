<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Response;

use Corra\Spreedly\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Model\InfoInterface as InfoInterfaceAlias;
use Magento\Sales\Model\Order\Payment;

abstract class AbstractResponseHandler
{
    public const TRANSACTION_ID = "gateway_transaction_id";
    /**
     * The token uniquely identifying this transaction payment method token
     */
    public const TOKEN_DATA = "token_data";
    public const KEY_TOKEN = 'token';
    public const KEY_GATEWAY_TOKEN = 'gateway_token';

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * AbstractResponseHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Get Valid Payment Response Data
     *
     * @param array $buildSubject
     * @return InfoInterfaceAlias
     */
    protected function getValidPaymentInstance(array $buildSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var InfoInterfaceAlias $payment */
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        return $payment;
    }

    /**
     * Handle Authorize Process
     *
     * @param Payment $payment
     * @param array $spreedlyResponse
     * @return Payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function handleAuthorizeResponse($payment, $spreedlyResponse)
    {
        /** @var $payment Payment */
        $tokenData = $this->buildTokenToSave($spreedlyResponse);
        $payment->setTransactionId($spreedlyResponse['transaction'][self::KEY_TOKEN]);
        $payment->setCcTransId($spreedlyResponse['transaction'][self::KEY_TOKEN]);

        if ($tokenData !== null) {
            $payment->setAdditionalInformation(self::TOKEN_DATA, $tokenData);
        }
        return $payment;
    }

    /**
     * Build Spreedly token data
     *
     * @param array $spreedlyResponse
     * @return null|array
     */
    protected function buildTokenToSave($spreedlyResponse)
    {
        /**
         * Avoid building because payment was placed with token
         */

        if (!isset($spreedlyResponse['transaction']['payment_method'][self::KEY_TOKEN])) {
            return null;
        }
        return [
            'payment_method_token' => $spreedlyResponse['transaction']['payment_method'][self::KEY_TOKEN],
            'gateway_token' => $spreedlyResponse['transaction'][self::KEY_GATEWAY_TOKEN],
            'gateway_transaction_id' => $spreedlyResponse['transaction'][self::TRANSACTION_ID]
        ];
    }
}
