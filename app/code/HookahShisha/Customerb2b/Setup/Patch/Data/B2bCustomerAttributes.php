<?php
namespace HookahShisha\Customerb2b\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class B2bCustomerAttributes implements DataPatchInterface, PatchRevertableInterface
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
            'cst_account_verified',
            [
                'type' => 'int',
                'label' => 'Account Verified?',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'required' => false,
                'visible' => true,
                'position' => 501,
                'system' => false,
                'backend' => '',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'cst_account_verified')->addData([
            'used_in_forms' => [
                'adminhtml_customer',
            ],
        ]);
        $attribute->save();

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'cst_details_changed',
            [
                'type' => 'int',
                'label' => 'Details Changed?',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default' => 1,
                'required' => false,
                'visible' => true,
                'position' => 500,
                'system' => false,
                'backend' => '',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $attributeDetails = $customerSetup->getEavConfig()->getAttribute('customer', 'cst_details_changed')
            ->addData([
                'used_in_forms' => [
                    'adminhtml_customer',
                ],
            ]);
        $attributeDetails->save();

        $note = "If you reject then please add message here
        Example : Some Of your details has been rejected. please update the same.";
        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'cst_verification_message',
            [
                'type' => 'varchar',
                'label' => 'Verification Message',
                'input' => 'text',
                'source' => '',
                'default' => "",
                'required' => false,
                'visible' => true,
                'position' => 502,
                'system' => false,
                'backend' => '',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'note' => $note,
            ]
        );

        $attributeVerification = $customerSetup->getEavConfig()->getAttribute('customer', 'cst_verification_message')
            ->addData([
                'used_in_forms' => [
                    'adminhtml_customer',
                ],
            ]);
        $attributeVerification->save();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'cst_account_verified');
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'cst_details_changed');
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'cst_verification_message');

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
