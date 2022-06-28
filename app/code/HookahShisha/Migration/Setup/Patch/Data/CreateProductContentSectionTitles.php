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
 * Create Title Attributes for Content Sections
 *
 * Class CreateProductContentSections
 */
class CreateProductContentSectionTitles implements DataPatchInterface, UninstallInterface
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
        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
        'type' => 'text',
        'input' => 'text',
        'group' => 'Content',
        'is_global' => true,
        'user_defined' => true,
        'required' => false,
        'is_used_in_grid' => false,
        'visible' => true,
        'visible_on_front' => true,
        'apply_to' => ''
    ];

    /**
     * @var array
     */
    private array $attributes = [
        'description_title' => [
            'label' => 'Description Section Title',
            'default' => 'Overview',
            'sort_order' => 10
        ],
        'whats_in_box_title' => [
            'label' => 'What\'s in the box Section Title',
            'default' => 'What\'s in the Box',
            'sort_order' => 30
        ],
        'assembly_care_title' => [
            'label' => 'Assembly and care Section Title',
            'default' => 'Assembly and Care',
            'sort_order' => 50
        ],
        'faq_title' => [
            'label' => 'FAQ\'s Section Title',
            'default' => 'FAQ',
            'sort_order' => 70
        ]
    ];

    /**
     * @var array
     */
    private array $attributesToUpdate = [
        'description' => [
            'sort_order' => 20
        ],
        'whats_in_box' => [
            'sort_order' => 40
        ],
        'assembly_care' => [
            'sort_order' => 60
        ],
        'faq' => [
            'sort_order' => 80
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

        // Sorting content attributes so that corresponding title attribute appears above
        foreach ($this->attributesToUpdate as $code => $config) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $code,
                'sort_order',
                $config['sort_order'],
                $config['sort_order']
            );
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
    public function getAliases(): array // NOSONAR
    {
        return [];
    }
}
