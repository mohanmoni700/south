<?php

namespace Avalara\Excise\Framework;

use Avalara\AddressResolutionModel;
use Avalara\Excise\Framework\ApiClient;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use Avalara\Excise\Helper\Config as ConfigHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

/**
 * Wrapper class for adding additional configurations or actions
 * @codeCoverageIgnore
 */
class AvalaraClientWrapper extends ApiClient
{
    /**
     * @var ConfigHelper
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    //protected $logger;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * AvalaraClientWrapper constructor.
     * @param DataObjectFactory $dataObjectFactory
     * @param LoggerInterface $logger
     * @param ConfigHelper $config
     * @param null $store
     * @param string $scope
     * @param null $type
     * @param null $mode
     * @param array $guzzleParams
     * @throws \Exception
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        LoggerInterface $logger,
        ConfigHelper $config,
        $store = null,
        $scope = ScopeInterface::SCOPE_STORE,
        $type = null,
        $mode = null,
        array $guzzleParams = []
    ) {
        $isSandbox = $mode == 1 ? Constants::API_MODE_DEV : Constants::API_MODE_PROD;
        if ($mode === null) {
            $isSandbox = $config->getCurrentModeString($store, $scope);
        } elseif ((substr($mode, 0, 8) == 'https://') || (substr($mode, 0, 7) == 'http://')) {
            $isSandbox = $mode;
        }

        parent::__construct(
            $config->getApplicationName(),
            $config->getApplicationVersion(),
            $isSandbox,
            $config->getApplicationDomain(),
            $type,
            $guzzleParams
        );
        $this->config = $config;
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritDoc}
     * @codeCoverageIgnore
     */
    protected function restCall($apiUrl, $verb, $guzzleParams, $getArray = false)
    {
        if (!\is_array($guzzleParams)) {
            $guzzleParams = [];
        }

        if (!isset($guzzleParams['timeout'])) {
            $guzzleParams['timeout'] = $this->config->getAvalaraApiTimeout();
        }

        // Warning: This causes the value to revert to the default "forever" timeout in guzzle
        if (\is_nan($guzzleParams['timeout'])) {
            $guzzleParams['timeout'] = 0;
        }
        $guzzleParams['debug'] = false;

        return parent::restCall($apiUrl, $verb, $guzzleParams, $getArray);
    }
}
