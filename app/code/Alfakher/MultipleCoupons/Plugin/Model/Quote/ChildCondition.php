<?php

namespace Alfakher\MultipleCoupons\Plugin\Model\Quote;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\RuleFactory;
use Psr\Log\LoggerInterface;

class ChildCondition
{
    const OPERATOR = ['==', '()'];

    const ATTRIBUTE_SCOPE = "parent";

    const BUNDLE_TYPE_ID = "bundle";

    const PRODUCT_CONDITION = "Condition\Product";

    protected Json $json;
    private RuleFactory $rule;

    protected LoggerInterface $logger;

    /**
     * @param Json $json
     */
    public function __construct(
        RuleFactory     $rule,
        LoggerInterface $logger,
        Json            $json
    )
    {
        $this->json = $json;
        $this->rule = $rule;
        $this->logger = $logger;
    }

    /**
     * Checking if there children calculated or parent item
     * when we have parent quote item and its children
     * @return bool
     */
    public function aroundIsChildrenCalculated(AbstractItem $subject, callable $proceed)
    {
        if ($subject->getParentItem()) {
            $calculate = $subject->getParentItem()->getProduct()->getPriceType();
        } else {
            $calculate = $subject->getProduct()->getPriceType();

            //Added the condition to apply discount on parent bundle product
            $sku = $subject->getProduct()->getSku();
            $appliedRuleIds = $subject->getQuote()->getAppliedRuleIds();
            if ($subject->getProduct()->getTypeId() == self::BUNDLE_TYPE_ID
                && $this->isParentCondition($sku, $appliedRuleIds)) {
                return false;
            }
            //End of the bundle discount condition
        }

        if (null !== $calculate && (int)$calculate === AbstractType::CALCULATE_CHILD) {
            return true;
        }

        return false;
    }

    /**
     * @param $sku
     * @param $appliedRuleIds
     * @return bool
     */
    public function isParentCondition($sku, $appliedRuleIds)
    {
        try {
            if (!empty($appliedRuleIds)) {
                $productCondition = $this->getProductCondition($appliedRuleIds);
                foreach ($productCondition as $ruleCondition) {
                    $skuList = $ruleCondition['value'] ? explode(',', $ruleCondition['value']) : [];
                    if (isset($ruleCondition['type'])
                        && str_ends_with($ruleCondition['type'], self::PRODUCT_CONDITION)
                        && $ruleCondition['attribute_scope'] == self::ATTRIBUTE_SCOPE
                        && ($ruleCondition['value'] == $sku || in_array($sku, $skuList))
                        && in_array($ruleCondition['operator'], self::OPERATOR)
                    ) {
                        return true;
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return false;
    }

    /**
     * @param $ruleIds
     * @return array|mixed
     */
    private function getProductCondition($ruleIds)
    {
        $ruleIds = explode(',', $ruleIds);
        foreach ($ruleIds as $ruleId) {
            $rule = $this->rule->create()->load($ruleId);
            $actionSerialized = $rule->getData('actions_serialized');
            if (!empty($actionSerialized)) {
                $condition = $this->json->unserialize($actionSerialized);
                //Return the first rule id as it is the last rule id applied
                return $condition['conditions'] ?? [];
            }
        }
        return [];
    }
}
