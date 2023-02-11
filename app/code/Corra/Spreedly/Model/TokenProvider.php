<?php

declare(strict_types=1);

namespace Corra\Spreedly\Model;

use Corra\Spreedly\Gateway\Config\Config;

/**
 * Token Provider for the Payment Gateway
 */
class TokenProvider
{
    /**
     * @var OrderDataProvider
     */
    protected $orderDataProvider;

    /** @var Config $config */
    protected $config;

    /**
     * TokenProvider constructor.
     * @param Config $config
     * @param OrderDataProvider $orderDataProvider
     */
    public function __construct(
        Config $config,
        OrderDataProvider $orderDataProvider
    ) {
        $this->config = $config;
        $this->orderDataProvider = $orderDataProvider;
    }

    /**
     * Get Gateway token
     *
     * @return string
     */
    public function getGatewayToken()
    {
        //check test mode or not
        if ($this->config->getTestMode()) {
            $token = $this->config->getTestGatewayToken();
        } else {
            //check both gateway active or not
            $payeezyActive = $this->config->getPayeezyGatewayActive();
            $authorizeNetActive = $this->config->getAuthorizenetGatewayActive();
            if ($payeezyActive && $authorizeNetActive) {
                //get totalOrder count on the same day
                $totalNumberOfOrdersToday = $this->orderDataProvider->getTodayOrdersCount();
                $totalDistributionRatio = $this->config->getPayeezyGatewayDistribution() +
                    $this->config->getAuthorizenetGatewayDistribution();
                //modulus operation and we get remainder value
                $remainder = fmod($totalNumberOfOrdersToday, $totalDistributionRatio);
                if ($remainder < $this->config->getPayeezyGatewayDistribution()) {
                    $token = $this->config->getPayeezyGatewayToken();
                } else {
                    $token = $this->config->getAuthorizeNetGatewayToken();
                }
            } elseif ($authorizeNetActive) {
                $token = $this->config->getAuthorizeNetGatewayToken();
            } else {
                $token = $this->config->getPayeezyGatewayToken();
            }
        }
        return $token;
    }
}
