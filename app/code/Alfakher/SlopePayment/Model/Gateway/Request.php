<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Model\Gateway;

use Magento\Framework\HTTP\Client\Curl;
use Alfakher\SlopePayment\Model\System\Config\Backend\Environment;
use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;

class Request
{
    const CONTENT_TYPE_JSON = 'application/json';

    /**
     * @var CurlFactory
     */
    protected $curl;

    public function __construct(
        Curl $curl,
        SlopeConfigHelper $slopeConfig
    ) {
        $this->curl = $curl;
        $this->config = $slopeConfig;
    }

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

    public function get($url)
    {
        $this->init();
        $this->curl->get($url);
        return $this->curl->getBody();
    }
    
    public function post($url, $data = null)
    {
        $this->init();
        $this->curl->post($url, $data);
        return $this->curl->getBody();
    }
}
