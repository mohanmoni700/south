<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model\Gateway;

use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Alfakher\SlopePayment\Model\System\Config\Backend\Environment;
use Magento\Framework\HTTP\Client\Curl;

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
     * Class constructor
     *
     * @param Curl $curl
     * @param SlopeConfigHelper $slopeConfig
     */
    public function __construct(
        Curl $curl,
        SlopeConfigHelper $slopeConfig
    ) {
        $this->curl = $curl;
        $this->config = $slopeConfig;
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
        return $this->curl->getBody();
    }
}
