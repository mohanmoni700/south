<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Model\Rewrite\SalesRule\Model;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\Validator\Pool;
use HookahShisha\Removefreegift\Model\SalesRule\RuleHelper;

class Validator extends \Magento\SalesRule\Model\Validator
{
    /**
     * Rule source collection
     *
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected $_rules;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    private RuleHelper $ruleHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param Data $catalogData
     * @param Utility $utility
     * @param RulesApplier $rulesApplier
     * @param PriceCurrencyInterface $priceCurrency
     * @param Pool $validators
     * @param ManagerInterface $messageManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param CartFixedDiscount|null $cartFixedDiscount
     */
    public function __construct(
        Context                $context,
        Registry               $registry,
        CollectionFactory      $collectionFactory,
        Data                   $catalogData,
        Utility                $utility,
        RulesApplier           $rulesApplier,
        PriceCurrencyInterface $priceCurrency,
        Pool                   $validators,
        ManagerInterface       $messageManager,
        RuleHelper             $ruleHelper,
        AbstractResource       $resource = null,
        AbstractDb             $resourceCollection = null,
        array                  $data = [],
        ?CartFixedDiscount     $cartFixedDiscount = null
    )
    {
        parent::__construct($context,
            $registry,
            $collectionFactory,
            $catalogData,
            $utility,
            $rulesApplier,
            $priceCurrency,
            $validators,
            $messageManager,
            $resource,
            $resourceCollection,
            $data,
            $cartFixedDiscount
        );
        $this->_collectionFactory = $collectionFactory;
        $this->rulesApplier = $rulesApplier;
        $this->ruleHelper = $ruleHelper;
    }

    /**
     * Quote item discount calculation process
     *
     * @param AbstractItem $item
     * @return $this
     */
    public function process(AbstractItem $item)
    {
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $child->setDiscountAmount(0);
                $child->setBaseDiscountAmount(0);
                $child->setDiscountPercent(0);
            }
        }

        $itemPrice = $this->getItemPrice($item);
        if ($itemPrice < 0) {
            return $this;
        }

        $appliedRuleIds = [];
        if ($this->getCouponCode()) {
            $appliedRuleIds = $this->rulesApplier->applyRules(
                $item,
                $this->_getRules($item->getAddress()),
                $this->_skipActionsValidation,
                $this->getCouponCode()
            );
        }

        //In order to fix the promo products that being removed (calling the promo product rules separately)
        $promoItemRuleIds = $this->rulesApplier->applyRules(
            $item,
            $this->_getPromoItemRules($item->getAddress()),
            $this->_skipActionsValidation,
            $this->getCouponCode()
        );

        $appliedRuleIds = array_merge($appliedRuleIds, $promoItemRuleIds);

        $this->rulesApplier->setAppliedRuleIds($item, $appliedRuleIds);

        return $this;
    }

    /**
     * Get rules of promo items
     *
     * @param Address|null $address
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected function _getPromoItemRules(Address $address = null)
    {
        $addressId = $this->getAddressId($address);
        $key = $this->getWebsiteId() . '_'
            . $this->getCustomerGroupId() . '_'
            . '_'
            . $addressId;
        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = $this->_collectionFactory->create()
                ->setValidationFilter(
                    $this->getWebsiteId(),
                    $this->getCustomerGroupId(),
                    '',
                    null,
                    $address
                )
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('simple_action', array('like' => '%ampromo%'))//Condition for promo rules only
                ->load();
        }
        return $this->_rules[$key];
    }

    /**
     * Calculate quote totals for each rule and save results
     *
     * @param mixed $items
     * @param Address $address
     * @return $this
     * @throws \Zend_Validate_Exception
     * @throws \Zend_Db_Select_Exception
     */
    public function initTotals($items, Address $address)
    {
        $address->setCartFixedRules([]);

        if (!$items) {
            return $this;
        }

        //To get the Promo skus
        $promoSkus = $this->getPromoSkus($address);

        /** @var Rule $rule */
        foreach ($this->_getRules($address) as $rule) {
            if (Rule::CART_FIXED_ACTION == $rule->getSimpleAction()
                && $this->validatorUtility->canProcessRule($rule, $address)
            ) {
                $ruleTotalItemsPrice = 0;
                $ruleTotalBaseItemsPrice = 0;
                $validItemsCount = 0;

                foreach ($items as $item) {
                    //Skipping child items and promo sku to avoid double calculations
                    if (!$this->isValidItemForRule($item, $rule) || in_array($item->getSku(), $promoSkus)) {
                        continue;
                    }

                    $qty = $this->validatorUtility->getItemQty($item, $rule);
                    $ruleTotalItemsPrice += $this->getItemPrice($item) * $qty;
                    $ruleTotalBaseItemsPrice += $this->getItemBasePrice($item) * $qty;
                    $validItemsCount++;
                }

                $this->_rulesItemTotals[$rule->getId()] = [
                    'items_price' => $ruleTotalItemsPrice,
                    'base_items_price' => $ruleTotalBaseItemsPrice,
                    'items_count' => $validItemsCount,
                ];
            }
        }
        return $this;
    }

    /**
     * Determine if quote item is valid for a given sales rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @return bool
     */
    private function isValidItemForRule(AbstractItem $item, Rule $rule)
    {
        if ($item->getParentItemId()) {
            return false;
        }
        if ($item->getParentItem()) {
            return false;
        }
        if (!$rule->getActions()->validate($item)) {
            return false;
        }
        if (!$this->canApplyDiscount($item)) {
            return false;
        }
        return true;
    }


    /**
     * To get the Promo skus
     * @param $address
     * @return array
     */
    public function getPromoSkus($address)
    {
        //Get the applied rule ids
        $appliedRuleIds = $address->getQuote()->getAppliedRuleIds();
        if (!empty($appliedRuleIds)) {
            $ruleIds = explode(',', $appliedRuleIds);
        }

        $promoSkus = [];
        if (isset($ruleIds)) {
            foreach ($ruleIds as $ruleId) {
                $rowId = $this->getRowIdByRuleId($ruleId);
                //Filter only the promo skus of the specified rule in the quote
                if (isset($rowId)) {
                    $skus =  $this->ruleHelper->getPromoSkus($rowId);
                    if (!empty($skus)) {
                        $skus = array_map('trim', explode(',', $skus));
                        $promoSkus = array_merge($promoSkus, $skus);
                    }
                }
            }
        }
        return $promoSkus;
    }

    /**
     * @param $ruleId
     * @return array|mixed|null
     */
    public function getRowIdByRuleId($ruleId)
    {
        return $this->_collectionFactory->create()
            ->addFieldToFilter('rule_id', ['eq' => $ruleId])
            ->getFirstItem()->getData('row_id');
    }
}
