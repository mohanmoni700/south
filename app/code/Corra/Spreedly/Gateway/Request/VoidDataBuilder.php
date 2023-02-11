<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;

class VoidDataBuilder extends AbstractDataBuilder
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

        return $this->config->getServiceUrl() .'transactions/'. $transactionId.'/void.json';
    }

    /**
     * @inheritdoc
     */
    public function getMethod(array $buildSubject)
    {
        return 'POST';
    }
    /**
     * The full settlement has no body.
     *
     * @param array $buildSubject
     * @return bool
     */
    public function getBody(array $buildSubject)
    {
        return false;
    }
}
