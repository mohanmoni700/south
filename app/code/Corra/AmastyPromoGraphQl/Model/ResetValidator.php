<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Model;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\Validator;

class ResetValidator extends Validator
{
    const NO_COUPON = 1;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param Data $catalogData
     * @param Utility $utility
     * @param RulesApplier $rulesApplier
     * @param PriceCurrencyInterface $priceCurrency
     * @param Validator\Pool $validators
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
        Validator\Pool         $validators,
        ManagerInterface       $messageManager,
        AbstractResource       $resource = null,
        AbstractDb             $resourceCollection = null,
        array                  $data = [],
        ?CartFixedDiscount     $cartFixedDiscount = null
    )
    {
        parent::__construct(
            $context,
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

    }

    /**
     * Reset quote and address applied rules
     *
     * @param Address $address
     * @return $this
     */
    public function reset(Address $address)
    {
        $this->validatorUtility->resetRoundingDeltas();
        $address->setBaseSubtotalWithDiscount($address->getBaseSubtotal());
        $address->setSubtotalWithDiscount($address->getSubtotal());
        if ($this->_isFirstTimeResetRun) {

            //To skip the removal of auto coupon- In case of multi-coupons
            $appliedRuleIds = $this->getAppliedRuleIds($address->getQuote());
            $address->setAppliedRuleIds($appliedRuleIds);
            $address->getQuote()->setAppliedRuleIds($appliedRuleIds);

            $this->_isFirstTimeResetRun = false;
        }
        return $this;
    }

    /**
     * @param $quote
     * @return string
     */
    public function getAppliedRuleIds($quote)
    {
        $appliedRuleIds = $quote->getAppliedRuleIds();
        if (!empty($appliedRuleIds)) {
            //Get All Rule Ids with No Coupon
            $noCouponsRuleIds = $this->_collectionFactory->create()
                ->addFieldToFilter('coupon_type', ['eq' => self::NO_COUPON])
                ->getAllIds();
            $ruleIds = explode(',', $appliedRuleIds);
            $appliedIds = [];
            //Checking whether the existing rule id is in the no coupon rule ids
            foreach ($ruleIds as $ruleId) {
                if (in_array($ruleId, $noCouponsRuleIds)) {
                    $appliedIds[] = $ruleId;
                }
            }
            return implode(',', $appliedIds);
        }
        return $appliedRuleIds;
    }

}
