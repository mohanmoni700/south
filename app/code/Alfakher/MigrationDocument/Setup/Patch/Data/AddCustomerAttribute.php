<?php
declare (strict_types = 1);

namespace Alfakher\MigrationDocument\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddCustomerAttribute implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;
    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        SetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'is_migration_documents',
            [
                'label' => 'Migration Document',
                'input' => 'boolean',
                'type' => 'int',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'required' => false,
                'position' => 333,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => false,
                'backend' => '',
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'is_migration_documents');
        $attribute->addData([
            'used_in_forms' => [
                'adminhtml_customer',
            ],
        ]);
        $attribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,

        ]);
        $attribute->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }
    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'is_migration_documents');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
