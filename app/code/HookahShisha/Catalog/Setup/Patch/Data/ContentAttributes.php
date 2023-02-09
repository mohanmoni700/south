<?php
declare (strict_types = 1);

namespace HookahShisha\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class ContentAttributes for adding additional pagebuilder attributes for product
 */
class ContentAttributes implements DataPatchInterface
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
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'your_stylish',
            [
                'type' => 'text',
                'label' => 'Your stylish OOKA',
                'input' => 'textarea',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Content',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'pagebuilder_enabled' => true,
                'is_html_allowed_on_front' => true,
                'wysiwyg_enabled' => true,
                'unique' => false,
                'apply_to' => '',
            ]
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            'your_stylish',
            [
                'is_pagebuilder_enabled' => 1,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'more_flavor',
            [
                'type' => 'text',
                'label' => 'More flavor for your OOKA',
                'input' => 'textarea',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Content',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'pagebuilder_enabled' => true,
                'is_html_allowed_on_front' => true,
                'wysiwyg_enabled' => true,
                'unique' => false,
                'apply_to' => '',
            ]
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            'more_flavor',
            [
                'is_pagebuilder_enabled' => 1,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'first_to_know',
            [
                'type' => 'text',
                'label' => 'Be the first to know',
                'input' => 'textarea',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Content',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'pagebuilder_enabled' => true,
                'is_html_allowed_on_front' => true,
                'wysiwyg_enabled' => true,
                'unique' => false,
                'apply_to' => '',
            ]
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            'first_to_know',
            [
                'is_pagebuilder_enabled' => 1,
            ]
        );

        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $allAttributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($allAttributeSetIds as $attributeSetId) {
            $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Content');
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupId,
                'your_stylish',
                null
            );
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupId,
                'more_flavor',
                null
            );
            $eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupId,
                'first_to_know',
                null
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
