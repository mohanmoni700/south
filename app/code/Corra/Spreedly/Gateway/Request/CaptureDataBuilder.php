<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;

class CaptureDataBuilder extends AbstractDataBuilder
{
    /**
     * @inheritdoc
     */
    public function getUrl(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $transactionId = $payment->getCcTransId();
        if (!$transactionId) {
            throw new LocalizedException(__('No transaction token to proceed capture.'));
        }

        return $this->config->getServiceUrl() .'transactions/'. $transactionId.'/capture.json';
    }

    /**
     * @inheritdoc
     */
    public function getMethod(array $buildSubject)
    {
        return 'POST';
    }

    /**
     * The full settlement / partial settlement
     *
     * @param array $buildSubject
     * @return bool | array
     */
    public function getBody(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        try {
            $amount = $this->subjectReader->readAmount($buildSubject);
        } catch (\InvalidArgumentException $e) {
            return false;
        }
        return [
            "transaction" => [
                "amount" => $this->formatAmount($amount),
                "currency_code" => $order->getCurrencyCode()
            ]
        ];
    }
}
