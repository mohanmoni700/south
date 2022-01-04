<?php
declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * Class Data for fetching store config values
 */
class Config
{
    protected const ENABLE_INTEGRATION = 'invoice_capture/settings/enabled';
    protected const CRON_ENABLED = 'invoice_capture/settings/cron_enabled';
    protected const BATCH_SIZE = 'invoice_capture/settings/batch_size';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Returns whether the feature is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            self::ENABLE_INTEGRATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns whether the cron is enabled or not.
     *
     * @return bool
     */
    public function isCronEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            self::CRON_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Batch Size
     *
     * @return string
     */
    public function getBatchSize()
    {
        return $this->scopeConfig->getValue(
            self::BATCH_SIZE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
