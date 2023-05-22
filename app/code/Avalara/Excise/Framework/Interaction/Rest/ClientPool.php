<?php

namespace Avalara\Excise\Framework\Interaction\Rest;

use Avalara\Excise\Framework\AvalaraClientWrapper;
use Avalara\Excise\Framework\AvalaraClientWrapperFactory;
use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Helper\Config;

class ClientPool
{
    const API_MODE_PROD = 'production';

    const API_MODE_DEV = 'sandbox';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var AvalaraClientWrapper
     */
    protected $avalaraClientWrapperFactory;

    /** @var array */
    protected $clients = [];

    /**
     * @param Config              $config
     * @param AvalaraClientWrapperFactory $avalaraClientWrapperFactory
     */
    public function __construct(
        Config $config,
        AvalaraClientWrapperFactory $avalaraClientWrapperFactory
    ) {
        $this->config = $config;
        $this->avalaraClientWrapperFactory = $avalaraClientWrapperFactory;
    }

    /**
     * @param bool     $isProduction
     * @param string   $scopeType
     * @param int|null $scopeId
     *
     * @return string
     */
    protected function getClientCacheKey($isProduction, $scopeType, $scopeId = null)
    {
        $cacheKey = $this->config->getMode($isProduction);

        if ($scopeId !== null) {
            $cacheKey .= "-{$scopeId}";
        }

        return "{$cacheKey}-{$scopeType}";
    }

    /**
     * Get an AvaTax REST API client object
     *
     * @param bool|null $isProduction
     * @param int|null  $scopeId
     * @param string    $scopeType
     * @codeCoverageIgnore
     * @return AvalaraClientWrapper
     * @throws \InvalidArgumentException
     */
    public function getClient(
        $isProduction = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ) {
        if ($isProduction === null) {
            $isProduction = $this->config->isProductionMode($scopeId, $scopeType);
        }

        $cacheKey = $this->getClientCacheKey($isProduction, $scopeType, $scopeId);

        if (!isset($this->clients[$cacheKey])) {
            /** @var AvalaraClientWrapper $avaTaxClient */
            $avaTaxClient = $this->avalaraClientWrapperFactory->create(
                [
                    'config' => $this->config,
                    'store' => $scopeId,
                    'scope' => $scopeType,
                    'type' => Constants::ADDRESS_API,
                    'mode' => $isProduction ? self::API_MODE_PROD : self::API_MODE_DEV
                ]
            );

            $accountNumber = $this->config->getExciseAccountNumber($scopeId, $scopeType);
            $licenseKey = $this->config->getExciseLicenseKey($scopeId, $scopeType);

            $avaTaxClient->withSecurity($accountNumber, $licenseKey);

            $this->clients[$cacheKey] = $avaTaxClient;
        }

        return $this->clients[$cacheKey];
    }
}
