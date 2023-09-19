<?php

declare (strict_types = 1);

/**
 * Patch to Create product title Attributes
 */
namespace HookahShisha\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class HookahProductSpecsAttributes for adding additional attributes for product
 */
class HookahProductSpecsTitleAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Attribute Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory          $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);

        $eavSetup->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            'South Smoke',
            10
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'vase_style_title',
            [
                'type' => 'text',
                'label' => 'Vase Style Title',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => '',
                'store_id' => '5',
                'group' => 'South Smoke',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 5,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'screw_on_vase_capability_title',
            [
                'type' => 'text',
                'label' => 'Screw on Vase Capability Title',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'group' => 'South Smoke',
                'store_id' => '5',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 15,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'multi_hose_capability_title',
            [
                'type' => 'text',
                'label' => 'Multi Hose Capability Title',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => '',
                'store_id' => '5',
                'group' => 'South Smoke',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 25,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'included_accessories_title',
            [
                'type' => 'text',
                'label' => 'Included Accessories Title',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => '',
                'store_id' => '5',
                'group' => 'South Smoke',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 35,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'colors_title',
            [
                'type' => 'text',
                'label' => 'Colors Title',
                'input' => 'text',
                'store_id' => '5',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => '',
                'group' => 'South Smoke',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 45,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'hookah_brand_title',
            [
                'type' => 'text',
                'label' => 'Hookah Brand Title',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => '',
                'store_id' => '5',
                'group' => 'South Smoke',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 55,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'hookah_size_title',
            [
                'type' => 'text',
                'label' => 'Hookah Size Title',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => '',
                'store_id' => '5',
                'group' => 'South Smoke',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 65,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'hookah_country_of_manufacture_title',
            [
                'type' => 'text',
                'label' => 'Hookah Country Of Manufacture Title',
                'input' => 'text',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'Source' => '',
                'store_id' => '5',
                'group' => 'South Smoke',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'is_html_allowed_on_front' => true,
                'unique' => false,
                'apply_to' => '',
                'sort_order' => 75,
                'is_global' => false,
                'is_used_in_grid' => false,
            ]
        );
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
}
