<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class CardDetailsHandler
 */
class TokenHandler extends AbstractResponseHandler implements HandlerInterface
{

    /**
     * Stores payment token into payment object
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);

        $token = $response['transaction']['payment_method'][self::KEY_TOKEN] ?? null;
        if (!$token) {
            return;
        }
        $tokenData = $this->buildTokenToSave($response);

        if ($tokenData !== null) {
            $payment->setAdditionalInformation(self::TOKEN_DATA, $tokenData);
        }
    }
}
