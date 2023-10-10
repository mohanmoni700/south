<?php

declare(strict_types=1);

namespace HookahShisha\Removefreegift\Model\SalesRule;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;

class ExtendedRulesApplier extends RulesApplier
{
    private ?ChildrenValidationLocator $childrenValidationLocator;
    protected RuleHelper $ruleHelper;

    /**
     * @param CalculatorFactory $calculatorFactory
     * @param ManagerInterface $eventManager
     * @param Utility $utility
     * @param ChildrenValidationLocator|null $childrenValidationLocator
     * @param DataFactory|null $discountDataFactory
     * @param RuleDiscountInterfaceFactory|null $discountInterfaceFactory
     * @param DiscountDataInterfaceFactory|null $discountDataInterfaceFactory
     */
    public function __construct(
        CalculatorFactory            $calculatorFactory,
        ManagerInterface             $eventManager,
        Utility                      $utility,
        ChildrenValidationLocator    $childrenValidationLocator = null,
        DataFactory                  $discountDataFactory,
        RuleDiscountInterfaceFactory $discountInterfaceFactory,
        DiscountDataInterfaceFactory $discountDataInterfaceFactory,
        RuleHelper $ruleHelper
    )
    {
        parent::__construct($calculatorFactory, $eventManager, $utility, $childrenValidationLocator, $discountDataFactory, $discountInterfaceFactory, $discountDataInterfaceFactory);
        $this->childrenValidationLocator = $childrenValidationLocator
            ?: ObjectManager::getInstance()->get(ChildrenValidationLocator::class);
        $this->ruleHelper = $ruleHelper;
    }


    /**
     * Apply rules to current order item
     *
     * @param AbstractItem $item
     * @param Collection $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyRules($item, $rules, $skipValidation, $couponCode)
    {
        $address = $item->getAddress();
        $appliedRuleIds = [];
        $this->discountAggregator = [];
        /* @var $rule Rule */
        foreach ($rules as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            if (!$skipValidation && !$rule->getActions()->validate($item)) {
                if (!$this->childrenValidationLocator->isChildrenValidationRequired($item)) {
                    continue;
                }
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }

            $this->applyRule($item, $rule, $address, $couponCode);

            //Ignore the applied rule ids for promo product
            if ($this->ruleHelper->validatePromoItem($rule, $item)) {
                continue;
            }

            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $appliedRuleIds;
    }


    /**
     * Apply Rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param mixed $couponCode
     * @return $this
     */
    protected function applyRule($item, $rule, $address, $couponCode)
    {
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            $cloneItem = clone $item;
            /**
             * validate without children
             */
            $applyAll = $rule->getActions()->validate($cloneItem);
            foreach ($item->getChildren() as $childItem) {
                if ($applyAll || $rule->getActions()->validate($childItem)) {
                    $discountData = $this->getDiscountData($childItem, $rule, $address);
                    $this->setDiscountData($discountData, $childItem);
                }
            }
        } else {
            $discountData = $this->getDiscountData($item, $rule, $address);
            $this->setDiscountData($discountData, $item);
        }

        $this->maintainAddressCouponCode($address, $rule, $couponCode);

        //Ignoring the discount description for promo item
        if (!$this->ruleHelper->validatePromoItem($rule, $item)) {
            $this->addDiscountDescription($address, $rule);
        }

        return $this;
    }
}
