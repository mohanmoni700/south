<?php
namespace Avalara\Excise\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface ;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;
    private $eavSetupFactory;
    private $quoteSetupFactory;
    private $salesSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
              
        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_product_tax_code')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_product_tax_code',
                [
                    'type' => 'int',
                    'label' => 'Excise Product Code',
                    'input' => 'select',
                    'source' => \Avalara\Excise\Model\Product\Attribute\Source\ProductTaxCode::class,
                    'frontend' => '',
                    'required' => true,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Excise Attributes',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => '',
                    'group' => 'Excise Attributes'
                ]
            );
        }
        
        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_purchase_unit_price')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_purchase_unit_price',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Purchase Unit Price',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Excise Attributes'
                ]
            );
        }
        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_purchase_line_amount')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_purchase_line_amount',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Purchase Line Amount',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Excise Attributes'
                ]
            );
        }
        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_quantity')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_quantity',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Unit Quantity',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => '1',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'frontend_class' => 'validate-digits',
                    'group' => 'Excise Attributes'
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_of_measure')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_of_measure',
                [
                    'type' => 'int',
                    'label' => 'Unit Of Measure',
                    'input' => 'select',
                    'source' => \Avalara\Excise\Model\Product\Attribute\Source\UnitOfMeasure::class,
                    'frontend' => '',
                    'required' => true,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Excise Attributes',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => '',
                    'group' => 'Excise Attributes'
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_qty_measure')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_qty_measure',
                [
                    'type' => 'int',
                    'label' => 'Unit Quantity Unit Of Measure',
                    'input' => 'select',
                    'source' => \Avalara\Excise\Model\Product\Attribute\Source\UnitQuantityUnitOfMeasure::class,
                    'frontend' => '',
                    'required' => true,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Excise Attributes',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => '',
                    'group' => 'Excise Attributes'
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_alt_prod_content')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_alt_prod_content',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Alternative Product Content',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'frontend_class' => 'validate-number',
                    'group' => 'Excise Attributes'
                ]
            );
        }
        
        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_volume')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_volume',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Unit Volume',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Excise Attributes'
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_vol_measure')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_vol_measure',
                [
                    'type' => 'int',
                    'label' => 'Unit Volume Unit Of Measure',
                    'input' => 'select',
                    'source' => \Avalara\Excise\Model\Product\Attribute\Source\UnitVolumeUnitOfMeasure::class,
                    'frontend' => '',
                    'required' => true,
                    'backend' => '',
                    'sort_order' => '30',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'default' => null,
                    'visible' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'group' => 'Excise Attributes',
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'option' => '',
                    'group' => 'Excise Attributes'
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $quoteSetup->addAttribute(
                'quote',
                'excise_tax_response_order',
                [
                    'type' => 'text',
                    'length' => null,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'order',
                'excise_tax_response_order',
                [
                    'type' => 'text',
                    'length' => null,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'invoice',
                'excise_tax_response_order',
                [
                    'type' => 'text',
                    'length' => null,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
            
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'creditmemo',
                'excise_tax_response_order',
                [
                    'type' => 'text',
                    'length' => null,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
        }
        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->addAttribute('customer_address', 'county', [
                'label' => 'County',
                'input' => 'text',
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'source' => '',
                'required' => false,
                'position' => 90,
                'visible' => true,
                'system' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                'frontend_input' => 'hidden',
                'backend' => ''
            ]);

            $attribute=$customerSetup->getEavConfig()
                ->getAttribute('customer_address', 'county')
                ->addData(['used_in_forms' => [
                    'adminhtml_customer_address',
                    'adminhtml_customer',
                    'customer_address_edit',
                    'customer_register_address',
                    'customer_address',
                ]
                ]);
            $attribute->save();
        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);

            $connection = $setup->getConnection();
            if ($connection->tableColumnExists('quote_address', 'county') === false) {
                $quoteSetup->addAttribute(
                    'quote_address',
                    'county',
                    [
                        'type' => 'varchar',
                        'length' => 255,
                        'visible' => false,
                        'required' => false,
                        'grid' => false
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '0.0.8', '<')) {
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $quoteSetup->addAttribute(
                'quote_item',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $quoteSetup->addAttribute(
                'quote_item',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $quoteSetup->addAttribute(
                'quote',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $quoteSetup->addAttribute(
                'quote',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'order_item',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'order_item',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'order',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
        }
        if (version_compare($context->getVersion(), '0.0.9', '<')) {
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute(
                'invoice_item',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'invoice_item',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'creditmemo_item',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'creditmemo_item',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'invoice',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'invoice',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'creditmemo',
                'sales_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );

            $salesSetup->addAttribute(
                'creditmemo',
                'excise_tax',
                [
                    'type' => 'varchar',
                    'length' => 255,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
        }
    }
}
