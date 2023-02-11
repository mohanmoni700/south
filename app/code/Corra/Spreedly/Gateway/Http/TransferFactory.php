<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * TransferFactory constructor.
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setMethod($request['method'])
            ->setHeaders($this->unpackHeaders($request['headers']))
            ->setBody($request['body'])
            ->setUri($request['url'])
            ->build();
    }

    /**
     * Setting All Headers Value to API
     *
     * @param array $headers
     * @return array
     */
    protected function unpackHeaders($headers)
    {
        $result = [];
        foreach ($headers as $header => $value) {
            $result []= sprintf("%s: %s", $header, $value);
        }
        return $result;
    }
}
