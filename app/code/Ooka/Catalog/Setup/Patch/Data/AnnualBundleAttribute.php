<?php

declare(strict_types=1);

namespace Ooka\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Psr\Log\LoggerInterface;

class AnnualBundleAttribute implements DataPatchInterface, UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    protected EavSetupFactory $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected ModuleDataSetupInterface $setup;

    protected LoggerInterface $logger;

    /**
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup,
        LoggerInterface $logger
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $setup;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            $eavSetup = $this->getSetup();
            $eavSetup->addAttribute(
                Product::ENTITY,
                'is_annual_bundle',
                [
                    'type' => 'int',
                    'label' => 'Is Annual Bundle',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'group' => 'Annual Bundle',
                    'sort_order' => 250,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0'
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->getSetup();
        $eavSetup->removeAttribute(Product::ENTITY, 'is_annual_bundle');
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
        //In case of Aliases
        return [];
    }
}
