<?php

namespace HookahShisha\Bundle\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Rma\Setup\RmaSetup;
use Magento\Rma\Setup\RmaSetupFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddRmaAttributes implements
    DataPatchInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var RmaSetupFactory
     */
    private $rmaSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param RmaSetupFactory $setupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RmaSetupFactory $setupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->rmaSetupFactory = $setupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        //Add Product's Attribute
        /** @var RmaSetup $installer */
        $installer = $this->rmaSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Prepare database before module installation
         */
        $installer->installEntities();

        /* setting alfa_is_bundle field in rma_item_entity table as a static attribute */
        $installer->addAttribute(
            'rma_item',
            'alfa_is_bundle',
            [
                'type' => 'text',
                'label' => 'Alfa Is Bundle Rma Item',
                'input' => 'text',
                'visible' => false,
                'sort_order' => 15,
                'position' => 15,
            ]
        );

        /** @var $migrationSetup \Magento\Framework\Module\Setup\Migration */
        $migrationSetup = $this->moduleDataSetup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'magento_rma_item_eav_attribute',
            'data_model',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->doUpdateClassAliases();

        $groupName = 'Autosettings';
        $entityTypeId = $installer->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $installer->getAttributeSetId($entityTypeId, 'Default');
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }
}
