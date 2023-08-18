<?php
/**
 *
 * @package     OOKA
 * @author      Air global
 * @link        https://air.global/
 */

declare(strict_types=1);

namespace Ooka\Customizations\Setup\Patch\Data;

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
 * Will create product attribute isBulkProduct
 *
 * Class IsBulkAttribute
 */
class IsBulkAttribute implements DataPatchInterface, UninstallInterface
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
            'is_bulk_product',
            [

                'sort_order' => 100,
                'label' => 'Is Bulk product',
                'default' => false,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'type' => 'int',
                'input' => 'boolean',
                'group' => 'General',
                'is_global' => false,
                'user_defined' => true,
                'required' => false,
                'is_used_in_grid' => false,
                'visible' => true,
                'visible_on_front' => true,
                'is_html_allowed_on_front' => false,
                'apply_to' => ''
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->getSetup();
        $eavSetup->removeAttribute(Product::ENTITY, 'is_bulk_product');
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
