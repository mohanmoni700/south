<?php

declare(strict_types=1);

namespace HookahShisha\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Create Addtocart_popup_text product attribute
 *
 * Class AddtocartPopupProductAttributes
 */
class AddtocartBlockProductAttribute implements DataPatchInterface, UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $setup;

    /**
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $setup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $eavSetup = $this->getSetup();
        $eavSetup->addAttribute(
            Product::ENTITY,
            'addtocart_popup_cms',
            [

                'sort_order' => 0,
                'label' => 'Add to cart Cms block',
                'default' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'type' => 'text',
                'input' => 'select',
                'source' => 'HookahShisha\Catalog\Model\Config\Source\Options',
                'group' => 'HS Custom attributes',
                'is_global' => false,
                'user_defined' => true,
                'required' => false,
                'is_used_in_grid' => false,
                'visible' => true,
                'visible_on_front' => true,
                'is_html_allowed_on_front' => false,
                'apply_to' => 'simple,configurable',

                /** Add a note */
                'note' => 'Cms block selected will be shown as popup when you add this product to cart,
                 if it is empty it will not show the popup'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->getSetup();
        $eavSetup->removeAttribute(Product::ENTITY, 'addtocart_popup_cms');
    }

    /**
     * Initializes EAV Setup factory
     *
     * @return EavSetup
     */
    private function getSetup(): EavSetup
    {
        return $this->eavSetupFactory->create(['setup' => $this->setup]);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
