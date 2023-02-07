<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model\Gateway;

use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Alfakher\SlopePayment\Model\System\Config\Backend\Environment;
use Magento\Framework\HTTP\Client\Curl;
use Alfakher\SlopePayment\Logger\Logger;

class Request
{
    public const CONTENT_TYPE_JSON = 'application/json';

    /**
     * Curl client
     *
     * @var Curl
     */
    protected $curl;

    /**
     * Config helper
     *
     * @var SlopeConfigHelper
     */
    protected $slopeConfig;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Class constructor
     *
     * @param Curl $curl
     * @param SlopeConfigHelper $slopeConfig
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        SlopeConfigHelper $slopeConfig,
        Logger $logger
    ) {
        $this->curl = $curl;
        $this->config = $slopeConfig;
        $this->logger = $logger;
    }

    /**
     * Initialize API paramas for curl request
     *
     * @return void
     */
    public function init()
    {
        $environment = $this->config->getEnvironmentType();

        if ($environment == Environment::ENVIRONMENT_SANDBOX) {
            $publicKey = $this->config->getSandboxPublicKey();
            $privateKey = $this->config->getSandboxPrivateKey();
        } else {
            $publicKey = $this->config->getProductionPublicKey();
            $privateKey = $this->config->getProductionPrivateKey();
        }

        $this->curl->addHeader("Content-Type", self::CONTENT_TYPE_JSON);
        $this->curl->setCredentials($publicKey, $privateKey);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Make get request
     *
     * @param string $url
     * @return void
     */
    public function get($url)
    {
        $this->init();
        $this->curl->get($url);
        if ($this->config->isDebugEnabled()) {
            $this->logger->info('GET URL: '.$url);
            $this->logger->info('GET Response: '.json_encode($this->curl->getBody()));
        }
        return $this->curl->getBody();
    }

    /**
     * Make post request
     *
     * @param string $url
     * @param array $data
     * @return void
     */
    public function post($url, $data = null)
    {
        $this->init();
        $this->curl->post($url, $data);
        if ($this->config->isDebugEnabled()) {
            $this->logger->info('POST URL: '.$url);
            $this->logger->info('POST DATA: '.json_encode($data));
            $this->logger->info('Post Response: '.json_encode($this->curl->getBody()));
        }
        return $this->curl->getBody();
    }
}
