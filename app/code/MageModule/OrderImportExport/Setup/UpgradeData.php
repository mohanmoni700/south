<?php

namespace MageModule\OrderImportExport\Setup;

use MageModule\OrderImportExport\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 *
 * @package MageModule\OrderImportExport\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * UpgradeData constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface      $configWriter
     * @param ConfigResource       $configResource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        ConfigResource $configResource
    ) {
        $this->scopeConfig    = $scopeConfig;
        $this->configWriter   = $configWriter;
        $this->configResource = $configResource;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.2.1') < 0) {
            $value = $this->scopeConfig->getValue('orderimportexport/settings/import');
            if ($value) {
                try {
                    $value = json_decode($value, true);
                } catch (\Exception $e) {
                    $value = [];
                }

                $this->configResource->saveConfig('import', $value);
                $this->configWriter->delete('orderimportexport/settings/import');
            }

            $value = $this->scopeConfig->getValue('orderimportexport/settings/export');
            if ($value) {
                try {
                    $value = json_decode($value, true);
                } catch (\Exception $e) {
                    $value = [];
                }

                $this->configResource->saveConfig('export', $value);
                $this->configWriter->delete('orderimportexport/settings/export');
            }
        }
    }
}
