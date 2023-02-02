<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Setup\Operation;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetupFactory;

class AddProductAttribute
{
    private $eavSetup;
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function execute($setup)
    {
        $this->addProductAttribute($setup);
    }

    /**
     * Add Product Attribute
     */
    private function addProductAttribute($setup)
    {
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->eavSetup->addAttributeGroup(
            Product::ENTITY,
            'Default',
            'Subscribe Now',
            '26'
        );

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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

        $this->eavSetup->addAttribute(
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
