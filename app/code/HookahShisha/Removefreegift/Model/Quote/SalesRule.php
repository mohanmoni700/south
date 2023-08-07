<?php

declare(strict_types=1);

namespace HookahShisha\Removefreegift\Model\Quote;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\SalesRule\Model\RuleFactory;
use Amasty\Promo\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;

class SalesRule
{
    protected CartRepositoryInterface $quoteRepository;
    protected RuleFactory $ruleFactory;
    protected CollectionFactory $collectionFactory;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CollectionFactory       $collectionFactory,
        RuleFactory             $ruleFactory
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->ruleFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $quoteId
     * @param $ruleRowId
     * @return bool|void
     */
    public function getSalesRuleIdByQuote($quoteId, $ruleRowId)
    {
        try {
            //Get the quote
            $quote = $this->quoteRepository->get($quoteId);

            //Get Rule Id By AmproRule Id
            $rule = $this->ruleFactory->create();
            $ruleId = $this->getRuleIdByRowId($rule, $ruleRowId);

            //Check whether the rule id already applied
            if (isset($ruleId) && !$this->isQuoteRuleExist($quote->getAppliedRuleIds(), $ruleId)) {
                $rule = $rule->load($ruleId);

                //Get All Quote Items and validate only the non-promo item
                foreach ($quote->getAllVisibleItems() as $quoteItem) {
                    $parentId = $quoteItem->getParentItemId();
                    if (!isset($parentId)) {
                        if ($rule->getId()
                            && $rule->getActions()->validate($quoteItem)
                            && $this->isPromoItem($quoteItem->getSku())) {
                            return true;
                        }
                        return false;
                    }
                }
            }
        } catch (\Exception $exception) {
            //In case of exception also it should return as true
        }
        return true;
    }

    /**
     * Function to determine the promo skus
     * @param $sku
     * @return bool
     */
    private function isPromoItem($sku): bool
    {
        $amPromoRule = $this->collectionFactory->create()
            ->addFieldToFilter('sku', ['finset' => $sku]);
        if ($amPromoRule->getSize() > 0) {
            return false;
        }
        return true;

    }

    /**
     * To get Rule Id by AmproRuleId
     * @param $rule
     * @param $ruleRowId
     * @return array|mixed|null
     */
    private function getRuleIdByRowId($rule, $ruleRowId)
    {
        /** @var Rule $rule */
        return $rule->getCollection()->addFieldToFilter('row_id', ['eq' => $ruleRowId])
            ->getFirstItem()->getData('rule_id');
    }

    private function isQuoteRuleExist($appliedRuleIds, $ruleId)
    {
        if (isset($appliedRuleIds) && !empty($appliedRuleIds)) {
            $appliedRuleIds = explode(',', $appliedRuleIds);
            if (in_array($ruleId, $appliedRuleIds)) {
                return true;
            }
        }
        return false;
    }
}
