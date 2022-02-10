<?php

namespace HookahShisha\Customerb2b\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class OtherContactsCustomerAttribute implements DataPatchInterface, PatchRevertableInterface
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
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'contact_name',
            [
                'type' => 'varchar',
                'label' => 'Other Contact Name',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => false,
                'position' => 500,
                'system' => false,
                'backend' => '',
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'contact_name')->addData([
            'used_in_forms' => [
                'adminhtml_customer',
            ],
        ]);
        $attribute->save();

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'contact_phone',
            [
                'type' => 'varchar',
                'label' => 'Other Contact Phone',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => false,
                'position' => 500,
                'system' => false,
                'backend' => '',
            ]
        );

        $attributePhone = $customerSetup->getEavConfig()->getAttribute('customer', 'contact_phone')->addData([
            'used_in_forms' => [
                'adminhtml_customer',
            ],
        ]);
        $attributePhone->save();

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'contact_email',
            [
                'type' => 'varchar',
                'label' => 'Other Contact Email',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => false,
                'position' => 500,
                'system' => false,
                'backend' => '',
            ]
        );

        $attributeEmail = $customerSetup->getEavConfig()->getAttribute('customer', 'contact_email')->addData([
            'used_in_forms' => [
                'adminhtml_customer',
            ],
        ]);
        $attributeEmail->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $customerSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'contact_name');
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'contact_phone');
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'contact_email');

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
        return [];
    }
}
