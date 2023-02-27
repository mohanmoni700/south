<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class TransactionCaptureHandler extends AbstractResponseHandler implements HandlerInterface
{
    private const RESPONSE_TOKEN = "token";

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setTransactionId($response['transaction'][self::RESPONSE_TOKEN]);

        $payment->setIsTransactionClosed(false);
        $payment->setIsTransactionPending(false);
        $payment->setIsFraudDetected(false);
    }
}
