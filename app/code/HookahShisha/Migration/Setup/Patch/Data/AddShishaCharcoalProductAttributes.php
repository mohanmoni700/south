<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    Janis Verins <info@corra.com>
 */
declare(strict_types=1);

namespace HookahShisha\Migration\Setup\Patch\Data;

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
 * Will create necessary product attributes for Shisha and Charcoal products
 *
 * Class AddShishaCharcoalProductAttributes
 */
class AddShishaCharcoalProductAttributes implements DataPatchInterface, UninstallInterface
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
    private array $config = [
        'sort_order' => 0,
        'global' => ScopedAttributeInterface::SCOPE_STORE,
        'type' => 'varchar',
        'input' => 'text',
        'group' => 'HS Custom attributes',
        'is_global' => false,
        'user_defined' => true,
        'required' => false,
        'is_used_in_grid' => false,
        'visible' => true,
        'visible_on_front' => true,
        'is_html_allowed_on_front' => false,
        'apply_to' => 'configurable'
    ];

    /**
     * @var array
     */
    private array $attributes = [
        'flavour' => [
            'label' => 'Flavour',
            'input' => 'select',
            'option' => [
                'values' => ['Apple', 'Apricot', 'Berry', 'Blueberry', 'Cappuccino', 'Cherry']
            ],
            'default' => ''
        ],
        'shisha_title' => [
            'label' => 'Shisha Title'
        ],
        'charcoal_title' => [
            'label' => 'Charcoal Title'
        ],
        'charcoal_short_detail' => [
            'label' => 'Charcoal Short Detail'
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
        $eavSetup = $this->getSetup();

        foreach ($this->attributes as $code => $config) {
            $eavSetup->addAttribute(Product::ENTITY, $code, array_replace($this->config, $config));
        }
    }

    /**
     * @inheritdoc
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->getSetup();

        foreach (array_keys($this->attributes) as $code) {
            $eavSetup->removeAttribute(Product::ENTITY, $code);
        }
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
