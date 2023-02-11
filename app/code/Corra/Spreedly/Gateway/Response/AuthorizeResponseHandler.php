<?php
/**
 * @author  CORRA
 */

namespace Corra\Spreedly\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class AuthorizeResponseHandler extends AbstractResponseHandler implements HandlerInterface
{
    /**
     * Handles fraud messages
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->getValidPaymentInstance($handlingSubject);

        $payment = $this->handleAuthorizeResponse($payment, $response);

        // Authorize transactions aren't closed. If we close it, it can't then be captured.
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
    }
}
