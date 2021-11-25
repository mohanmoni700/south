<?php

namespace HookahShisha\Bundle\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;

class UpdateAlfaIsBundleAttributes implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * AddAlfaIsBundleAttributes constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $salesSetup->addAttribute('shipment_item', 'alfa_is_bundle', ['type' => 'text']);
        $salesSetup->addAttribute('invoice_item', 'alfa_is_bundle', ['type' => 'text']);
        $salesSetup->addAttribute('creditmemo_item', 'alfa_is_bundle', ['type' => 'text']);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '1.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
