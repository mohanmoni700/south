<?php

declare(strict_types=1);

namespace HookahShisha\Removefreegift\Model\Quote;

use Magento\Quote\Model\QuoteRepository;
use Magento\SalesRule\Model\RuleFactory;
use Amasty\Promo\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Checkout\Model\Session;

class SalesRule
{
    protected RuleFactory $ruleFactory;

    protected CollectionFactory $collectionFactory;

    protected Session $checkoutSession;

    protected $quote;

    protected QuoteRepository $quoteRepository;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Session $checkoutSession
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Session           $checkoutSession,
        QuoteRepository   $quoteRepository,
        RuleFactory       $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param $quoteId
     * @param $ruleRowId
     * @return bool|void
     */
    public function getSalesRuleIdByQuote($quoteId, $ruleRowId, $sku)
    {
        try {
            //Get Rule Id By AmproRule Id
            $rule = $this->ruleFactory->create();
            $ruleId = $this->getRuleIdByRowId($rule, $ruleRowId);

            //Check promo rule id exist
            if (isset($ruleId)) {
                //Get the quote
                if (!$this->quote || ($this->quote->getId() != $this->checkoutSession->getQuote()->getId())) {
                    $this->quote = $this->quoteRepository->get($quoteId);
                }

                //Check whether the rule id already applied
                if (!$this->isQuoteRuleExist($this->quote->getAppliedRuleIds(), $ruleId)) {
                    $rule = $rule->load($ruleId);

                    //Get All Quote Items and validate only the non-promo item
                    return $this->isValidPromoItem($this->quote, $rule, $sku);
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
            return true;
        }
        return false;

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
        if (!empty($appliedRuleIds)) {
            $appliedRuleIds = explode(',', $appliedRuleIds);
            if (in_array($ruleId, $appliedRuleIds)) {
                return true;
            }
        }
        return false;
    }

    /**
     * To validate the promo item
     * @param $quote
     * @param $rule
     * @return bool
     */
    private function isValidPromoItem($quote, $rule, $sku)
    {
        //Get All items to check promo item
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $parentId = $quoteItem->getParentItemId();
            if (!isset($parentId) && $quoteItem->getSku() == $sku) {
                if ($rule->getId()
                    && $rule->getActions()->validate($quoteItem)
                    && $this->isPromoItem($quoteItem->getSku())) {
                    return true;
                }
                return false;
            }
        }
        return true;
    }
}
