<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Model\Ui;

use Corra\Spreedly\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Corra\Spreedly\Model\TokenProvider;

/**
 * Config provider for the payment method
 */
class ConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'spreedly';
    public const CC_VAULT_CODE = 'spreedly_cc_vault';

    /**
     * @var array
     */
    public const ADDITIONAL_DATA = [
        'payment_method_token',
        'is_active_payment_token_enabler',
        'cc_cid',
        'cc_number',
        'cc_exp_month',
        'cc_exp_month',
        'cc_exp_year'
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var TokenProvider
     */
    private $tokenProvider;

    /**
     * Inject all needed object for getting data from config
     *
     * @param Config $config
     * @param SessionManagerInterface $session
     * @param TokenProvider $tokenProvider
     */
    public function __construct(
        Config $config,
        SessionManagerInterface $session,
        TokenProvider $tokenProvider
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->tokenProvider = $tokenProvider;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();
        $isActive = $this->config->isActive($storeId);
        return [
            'payment' => [
                self::CODE => [
                    'GatewayToken' => $this->tokenProvider->getGatewayToken(),
                    "test_mode" => $this->config->getTestMode(),
                    "active" => $isActive,
                ]
            ]
        ];
    }
}
