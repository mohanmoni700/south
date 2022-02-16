<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Migration
 * @author    CORRA
 */
declare(strict_types=1);

namespace HookahShisha\Migration\Setup\Patch\Data;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Will create migrate_customer attribute
 *
 * Class AddMigrateCustomerAttribute
 */
class AddMigrateCustomerAttribute implements DataPatchInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private CustomerSetupFactory $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $setup;

    private const ATTRIBUTE_CODE = 'migrate_customer';

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function apply()
    {
        $customerSetup = $this->getSetup();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'label' => 'Migrate Customer',
                'input' => 'boolean',
                'source' => Boolean::class,
                'required' => false,
                'visible' => true,
                'system' => false,
                'backend' => '',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'default' => 0
            ]
        );

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(Customer::ENTITY, self::ATTRIBUTE_CODE)
            ->addData(['used_in_forms' => ['adminhtml_customer']]);

        $attribute->save();
    }

    /**
     * Uninstall existing attribute before creation
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetup = $this->getSetup();

        $customerSetup->removeAttribute(Customer::ENTITY, self::ATTRIBUTE_CODE);
    }

    /**
     * Initializes CustomerSetup factory
     *
     * @return CustomerSetup
     */
    private function getSetup(): CustomerSetup
    {
        return $this->customerSetupFactory->create(['setup' => $this->setup]);
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
