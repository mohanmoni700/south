<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\Subscribenow\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config;

class AddProductAttributePatch implements DataPatchInterface
{
   private $_moduleDataSetup;

   private $_eavSetupFactory;

   /**
    * @var Config
    */
    private $eavConfig;

   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup,
       EavSetupFactory $eavSetupFactory,
       Config $eavConfig
   ) {
       $this->_moduleDataSetup = $moduleDataSetup;
       $this->_eavSetupFactory = $eavSetupFactory;
       $this->eavConfig = $eavConfig;
   }

   public function apply()
   {
       /** @var EavSetup $eavSetup */
       $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_moduleDataSetup]);

       $eavSetup->addAttributeGroup(
        Product::ENTITY,
        'Default',
        'Subscribe Now',
        '26'
        );

        if(!$this->isAttributeExists('is_subscription')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'is_subscription',
                [
                    'type' => 'int',
                    'label' => 'Enable Subscribe Now',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => true, // Magedelight 2352016 to apply discount for subscription product
                    'source' => 'Magedelight\Subscribenow\Model\Source\SusbscriptionOption',
                    'default' => 0
                ]
            );
        }

        if(!$this->isAttributeExists('subscription_type')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'subscription_type',
                [
                    'type' => 'varchar',
                    'label' => 'Product Purchase Option',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => true,
                    'source' => 'Magedelight\Subscribenow\Model\Source\PurchaseOption',
                    'sort' => 20
                ]
            );
        }

        if(!$this->isAttributeExists('discount_type')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'discount_type',
                [
                    'type' => 'varchar',
                    'label' => 'Discount Type',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => true,
                    'source' => 'Magedelight\Subscribenow\Model\Source\DiscountType',
                    'sort' => 30
                ]
            );
        }

        if(!$this->isAttributeExists('discount_amount')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'discount_amount',
                [
                    'type' => 'decimal',
                    'label' => 'Discount On Subscription',
                    'input' => 'price',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'class' => 'validate-greater-than-zero',
                    'comment' => 'Discount will applied on product price.',
                    'sort' => 40
                ]
            );
        }

        if(!$this->isAttributeExists('initial_amount')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'initial_amount',
                [
                    'type' => 'decimal',
                    'label' => 'Initial Fee',
                    'input' => 'price',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'class' => 'validate-number validate-greater-than-zero',
                    'sort' => 50
                ]
            );
        }

        if(!$this->isAttributeExists('billing_period_type')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'billing_period_type',
                [
                    'type' => 'varchar',
                    'label' => 'Billing Period Defined By',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => true,
                    'source' => 'Magedelight\Subscribenow\Model\Source\BillingPeriodBy',
                    'sort' => 60
                ]
            );
        }

        if(!$this->isAttributeExists('billing_period')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'billing_period',
                [
                    'type' => 'varchar',
                    'label' => 'Billing Frequency',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => true,
                    'source' => 'Magedelight\Subscribenow\Model\Source\SubscriptionInterval',
                    'sort' => 70
                ]
            );
        }

        if(!$this->isAttributeExists('billing_max_cycles')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'billing_max_cycles',
                [
                    'type' => 'text',
                    'label' => 'Max Billing Cycle',
                    'backend' => '\Magedelight\Subscribenow\Model\Attribute\Backend\NumberOfBillingCycle',
                    'input' => 'text',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'class' => 'validate-number validate-digits validate-greater-than-zero',
                    'sort' => 80
                ]
            );
        }
        if(!$this->isAttributeExists('define_start_from')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'define_start_from',
                [
                    'type' => 'varchar',
                    'label' => 'Subscription Start From',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => true,
                    'source' => 'Magedelight\Subscribenow\Model\Source\SubscriptionStart',
                    'sort' => 90
                ]
            );
        }
        if(!$this->isAttributeExists('day_of_month')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'day_of_month',
                [
                    'type' => 'text',
                    'backend' => '\Magedelight\Subscribenow\Model\Attribute\Backend\Dayofmonth',
                    'label' => 'Day Of Month',
                    'input' => 'text',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'class' => 'validate-greater-than-zero validate-digits-range digits-range-1-31',
                    'sort' => 100
                ]
            );
        }

        if(!$this->isAttributeExists('allow_update_date')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'allow_update_date',
                [
                    'type' => 'int',
                    'label' => 'Allow Subscribers To Update Next Subscription Date',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => false,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => false,
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'sort' => 110
                ]
            );
        }

        if(!$this->isAttributeExists('allow_trial')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'allow_trial',
                [
                    'type' => 'int',
                    'label' => 'Trial Billing Period',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => true,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => false,
                    'source' => 'Magedelight\Subscribenow\Model\Source\TrialOption',
                    'sort' => 120,
                    'default' => 0
                ]
            );
        }

        if(!$this->isAttributeExists('trial_period')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'trial_period',
                [
                    'type' => 'varchar',
                    'label' => 'Trial Period',
                    'input' => 'select',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => false,
                    'visible_on_front' => false,
                    'is_used_for_promo_rules' => true,
                    'source' => 'Magedelight\Subscribenow\Model\Source\SubscriptionInterval',
                    'sort' => 130
                ]
            );
        }

        if(!$this->isAttributeExists('trial_amount')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'trial_amount',
                [
                    'type' => 'text',
                    'backend' => '\Magedelight\Subscribenow\Model\Attribute\Backend\TrialBillingAmount',
                    'label' => 'Trial Billing Amount',
                    'input' => 'text',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => false,
                    'visible_on_front' => false,
                    'class' => 'validate-number validate-zero-or-greater',
                    'sort' => 140
                ]
            );
        }

        if(!$this->isAttributeExists('trial_maxcycle')) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'trial_maxcycle',
                [
                    'type' => 'text',
                    'backend' => '\Magedelight\Subscribenow\Model\Attribute\Backend\NumberOfTrialCycle',
                    'label' => 'Number Of Trial Cycle',
                    'input' => 'text',
                    'required' => false,
                    'global' => Attribute::SCOPE_GLOBAL,
                    'group' => 'Subscribe Now',
                    'used_in_product_listing' => false,
                    'visible_on_front' => false,
                    'class' => 'validate-greater-than-zero',
                    'sort' => 150
                ]
            );
        }
   }

   /**
     * @param $field
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttributeExists($field)
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);

        return ($attr && $attr->getId());
    }

   public static function getDependencies()
   {
       return [];
   }

   public function getAliases()
   {
       return [];
   }
}