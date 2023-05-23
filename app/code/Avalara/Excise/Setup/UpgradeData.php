<?php
/**
 * A Magento 2 module named Avalara/Excise
 * Copyright (C) 2019
 *
 * This file included in Avalara/Excise is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */
namespace Avalara\Excise\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface ;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Avalara\Excise\Model\Config\Source\CustomerType;
use Avalara\Excise\Model\Config\Source\EntityUseCode;
use Avalara\Excise\Model\EntityUseCodeFactory;
use Avalara\Excise\Api\Rest\ListEntityUseCodesInterface;
use Avalara\Excise\Framework\Constants;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Adds and updates attributes for AvaTax
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;
    private $eavSetupFactory;
    private $quoteSetupFactory;
    private $salesSetupFactory;

    /**
     * @var Avalara\Excise\Model\EntityUseCodeFactory
     */
    protected $entityUseCodeFactory;
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
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        EntityUseCodeFactory $_entityUseCodeFactory,
        ListEntityUseCodesInterface $entityUseCodesInterface
    ) {
        $this->quoteSetupFactory    = $quoteSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->salesSetupFactory    = $salesSetupFactory;
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->_entityUseCodeFactory = $_entityUseCodeFactory;
        $this->entityUseCodesInterface = $entityUseCodesInterface;
    }
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
                
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
        $alternatePriceAttributes = [
            'excise_alternate_price_1' => 'Alternate Unit Price 1',
            'excise_alternate_price_2' => 'Alternate Unit Price 2',
            'excise_alternate_price_3' => 'Alternate Unit Price 3',
        ];
        foreach ($alternatePriceAttributes as $code=>$label) {
            if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, $code)) {
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $code,
                    [
                        'type' => 'varchar',
                        'backend' => '',
                        'frontend' => '',
                        'label' => $label,
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

        if (version_compare($context->getVersion(), '0.0.10', '<')) {
            if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_product_tax_code')) {
                $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_product_tax_code');
            }
        }

        if (version_compare($context->getVersion(), '0.0.11', '<')) {
            $attributeArray = [
                                'customer_type' => [
                                    'label' => 'Customer Type',
                                    'model' => CustomerType::class
                                ],
                                'entity_use_code' => [
                                    'label' => 'Entity Use Code',
                                    'model' => EntityUseCode::class
                                ]
            ];
            $this->addCustomerTypeAndEntityUseCode($setup, $attributeArray);
        }

        if (version_compare($context->getVersion(), '0.0.12', '<')) {
            if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_qty_measure')) {
                $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_qty_measure');
            }
            if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_vol_measure')) {
                $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_vol_measure');
            }
        }

        if (version_compare($context->getVersion(), '0.0.13', '<')) {
            if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_quantity')) {
                $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_quantity');
            }
            if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_unit_volume')) {
                $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_unit_volume');
            }
            if ($eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'excise_purchase_line_amount')) {
                $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'excise_purchase_line_amount');
            }
            
        }

        // Code to insert data in table entity use code 
        if (version_compare($context->getVersion(), '0.0.16', '<')) {
            // Get response from api
            $type = Constants::AVALARA_API;
            $entityUseCodesResponse = $this->entityUseCodesInterface->getEntityUseCodes(null, $type);

            if($entityUseCodesResponse){
                //Code to insert entity use codes from API response
                $result = $this->_entityUseCodeFactory->create();
                $collection = $result->getCollection();
                $tableName = $collection->getResource()->getMainTable();
                $conn = $collection->getConnection();

                // truncate the table first
                $conn->truncateTable($tableName);
                // Create data to insert into teh table
                $insertArray = $this->getInsertData($entityUseCodesResponse);
                $conn->insertMultiple($tableName, $insertArray);
            }
        }
    }
    
    /**
     * Add customer entity type and entity use code attributes
     *
     * @param   ModuleDataSetupInterface  $setup         
     * @param   array                     $attributeArray
     *
     * @return  $this
     */
    private function addCustomerTypeAndEntityUseCode($setup, $attributeArray)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        foreach ($attributeArray as $attributeCode => $attributeData){
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, [
                'type' => 'text',
                'label' => $attributeData['label'],
                'input' => 'select',
                'source' => $attributeData['model'],
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 30,
                'position' => 30,
                'system' => 0,
            ]);
    
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode)
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer'],
                ]);
            $attribute->save();
        }
        return $this;
    }

    /**
     * get data entity use code 
     *
     * @param   array   $entityUseCodesResponse
     *
     * @return  $optionArr
    */
    private function getInsertData($entityUseCodesResponse)
    {
        foreach ($entityUseCodesResponse as $value) {
            $optionArr[] = [
                'code' => $value['code'],
                'name' => $value['code'] .' - '. $value['name']                 
            ];
        }
        return $optionArr;
    }
}
