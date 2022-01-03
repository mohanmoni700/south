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
    const ENABLE_INTEGRATION = 'invoice_capture/settings/enabled';
    const CRON_ENABLED = 'invoice_capture/settings/cron_enabled';
    const BATCH_SIZE = 'invoice_capture/settings/batch_size';
    const ORDER_START_DATE = 'invoice_capture/settings/order_start';
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

    /**
     * Return Order Start Date
     *
     * @return string
     */
    public function getOrderStartDate()
    {
        return $this->scopeConfig->getValue(
            self::ORDER_START_DATE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
