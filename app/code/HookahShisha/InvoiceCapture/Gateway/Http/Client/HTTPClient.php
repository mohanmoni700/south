<?php
declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Gateway\Http\Client;

use Magento\Payment\Gateway\Command\CommandException;

/**
 * Class HTTPClient
 */
class HTTPClient
{
    /**
     * @param \Corra\Spreedly\Gateway\Http\Client\HTTPClient $subject
     * @param $response
     * @return mixed
     * @throws CommandException
     */
    public function afterPlaceRequest(\Corra\Spreedly\Gateway\Http\Client\HTTPClient $subject, $response)
    {
        if (isset($response['transaction']['succeeded']) &&
            $response['transaction']['succeeded'] == false &&
            isset($response['transaction']['message']) &&
            !empty($response['transaction']['message'])
        ) {
            throw new CommandException(
                __($response['transaction']['message'])
            );
        }
        return $response;
    }
}
