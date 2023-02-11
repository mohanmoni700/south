<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Corra\Spreedly\Gateway\Config\Config;
use Corra\Spreedly\Gateway\Helper\SubjectReader;
use Corra\Spreedly\Model\TokenProvider;

abstract class AbstractDataBuilder implements BuilderInterface
{
    /**
     * @var TokenProvider
     */
    protected $tokenProvider;

    /** @var Config $config */
    protected $config;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * AbstractDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param TokenProvider $tokenProvider
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config,
        TokenProvider $tokenProvider
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            'url' => $this->getUrl($buildSubject),
            'method' => $this->getMethod($buildSubject),
            'headers' => $this->getHeaders($buildSubject),
            'body' => $this->getBody($buildSubject)
        ];
    }

    /**
     * Get the Url of the API Endpoint
     *
     * @param array $buildSubject
     * @return mixed
     */
    abstract protected function getUrl(array $buildSubject);

    /**
     * Get the Method(POST / GET) of Data need to send it in the API
     *
     * @param array $buildSubject
     * @return mixed
     */
    abstract protected function getMethod(array $buildSubject);

    /**
     * Get Headers Value that need to send it in the API
     *
     * @param array $buildSubject
     * @return array
     */
    protected function getHeaders(array $buildSubject)
    {
        $authorization = base64_encode(
            sprintf('%s:%s', $this->config->getEnvironmentKey(), $this->config->getEnvironmentSecretKey())
        );
        return [
            'Authorization' => sprintf('Basic %s', $authorization),
            'Content-type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }

    /**
     * Get Body Data that need to send it in the API
     *
     * @param array $buildSubject
     * @return mixed
     */
    abstract protected function getBody(array $buildSubject);

    /**
     * Amount to request, as an integer. E.g., 1000 for $10.00
     *
     * @param float $amount
     * @return int
     */
    protected function formatAmount($amount)
    {
        return (int)number_format($amount, 2, '', '');
    }

    /**
     * Get Gateway token
     *
     * @return string
     */
    protected function getGatewayToken()
    {
        return $this->tokenProvider->getGatewayToken();
    }
}
