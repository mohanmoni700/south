<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RequireSerialNumberAttribute implements DataPatchInterface
{
    private const PRODUCT_OOKA_REQUIRE_SERIAL_NUMBER = 'ooka_require_serial_number';
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Apply method for attribute
     *
     * @return RequireSerialNumberAttribute|void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->addAttribute($eavSetup);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Adding attribute
     *
     * @param EavSetup $eavSetup
     * @return void
     * @throws LocalizedException
     */
    public function addAttribute($eavSetup)
    {
        if (!$this->isProductAttributeExists(self::PRODUCT_OOKA_REQUIRE_SERIAL_NUMBER)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                self::PRODUCT_OOKA_REQUIRE_SERIAL_NUMBER,
                [
                    'type' => 'int',
                    'label' => 'Ooka Require Serial Number',
                    'frontend_class' => '',
                    'backend' => Product\Attribute\Backend\Boolean::class,
                    'input' => 'boolean',
                    'sort_order' => '101',
                    'source' => Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'default' => true,
                    'visible' => true,
                    'user_defined' => true,
                    'required' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => 'simple',
                    'group' => 'General',
                    'used_in_product_listing' => true,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true
                ]
            );
        }
    }

    /**
     * Returns true if attribute exists and false if it doesn't exist
     *
     * @param string $field
     * @return bool
     * @throws LocalizedException
     */
    public function isProductAttributeExists(string $field): bool
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);
        return ($attr && $attr->getId());
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
