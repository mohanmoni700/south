<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;

class RefundDataBuilder extends AbstractDataBuilder
{
    /**
     * Root element
     */
    private const TRANSACTION_ROOT_ELEMENT = "transaction";

    /**
     * The amount to request, as an integer. E.g., 1000 for $10.00.
     */
    private const AMOUNT = "amount";

    /**
     * The currency of the funds, e.g., USD for US dollars.
     */
    private const CURRENCY_CODE = "currency_code";

    /**
     * @inheritdoc
     */
    public function getUrl(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $transactionId =  $payment->getParentTransactionId();
        if (!$transactionId) {
            throw new LocalizedException(__('No transaction token to proceed capture.'));
        }
        return $this->config->getServiceUrl() .'transactions/'. $transactionId.'/credit.json';
    }

    /**
     * @inheritdoc
     */
    public function getMethod(array $buildSubject)
    {
        return 'POST';
    }
    /**
     * The partial / full amount Refund
     *
     * @param array $buildSubject
     * @return bool
     */
    public function getBody(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $amount = null;
        $this->subjectReader->readAmount($buildSubject);
        try {
            $amount = $this->subjectReader->readAmount($buildSubject);
        } catch (\InvalidArgumentException $e) {
            $amount = $payment->getBaseAmountAuthorized();
        }

        return [
            self::TRANSACTION_ROOT_ELEMENT => [
                self::AMOUNT => $this->formatAmount($amount),
                self::CURRENCY_CODE => $order->getCurrencyCode()
            ]
        ];
    }
}
