<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    Bashid
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
 * Class UpdateShishaCharcoalProductAttributesForSimple
 */
class UpdateShishaCharcoalProductAttributesForSimple implements DataPatchInterface
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
     * @var array
     */
    private array $attributes = [
        'shisha_title' => [
            'apply_to' => 'simple'
        ],
        'charcoal_title' => [
            'apply_to' => 'simple'
        ]
    ];

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

        foreach ($this->attributes as $code => $configs) {
            foreach ($configs as $config => $value) {
                $eavSetup->updateAttribute(Product::ENTITY, $code, $config, $value);
            }
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
