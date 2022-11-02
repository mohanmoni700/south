<?php
declare(strict_types=1);

namespace HookahShisha\Customerb2b\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Customer\Model\Customer;

class HubCustomerAttributes implements DataPatchInterface, PatchRevertableInterface
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
            'hub_mobile_number',
            [
                'type' => 'varchar',
                'label' => 'Mobile Number',
                'input' => 'text',
                'source' => '',
                'default' => "",
                'required' => false,
                'visible' => false,
                'position' => 502,
                'system' => false,
                'backend' => '',
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'hub_mobile_number');

        $attribute->save();

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
        $customerSetup->removeAttribute(Customer::ENTITY, 'hub_mobile_number');
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
