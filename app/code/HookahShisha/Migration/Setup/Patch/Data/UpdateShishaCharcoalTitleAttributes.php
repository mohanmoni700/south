<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    Janis Verins <info@corra.com>
 */
declare(strict_types=1);

namespace HookahShisha\Migration\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Will update necessary product attributes for Shisha and Charcoal products
 *
 * Class UpdateShishaCharcoalTitleAttribute
 */
class UpdateShishaCharcoalTitleAttributes implements DataPatchInterface
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
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
        $attributes = ['shisha_title', 'charcoal_title'];

        foreach ($attributes as $code) {
            $eavSetup->updateAttribute(Product::ENTITY, $code, 'apply_to', 'simple,configurable');
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            AddShishaCharcoalProductAttributes::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
