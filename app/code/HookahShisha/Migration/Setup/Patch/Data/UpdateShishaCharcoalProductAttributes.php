<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    Janis Verins <info@corra.com>
 */
declare(strict_types=1);

namespace HookahShisha\Migration\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Will update necessary product attributes for Shisha and Charcoal products
 *
 * Class AddShishaCharcoalProductAttributes
 */
class UpdateShishaCharcoalProductAttributes implements DataPatchInterface
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
        'flavour' => [
            'apply_to' => 'simple',
            'source_model' => Table::class,
            'backend_type' => 'int'
        ],
        'charcoal_short_detail' => [
            'apply_to' => 'simple'
        ],
        'shisha_title' => [
            'default' => 'Included Premium Shisha Flavor'
        ],
        'charcoal_title' => [
            'default' => 'Free Hookah Charcoal'
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
