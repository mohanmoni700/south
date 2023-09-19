<?php

declare(strict_types=1);

namespace HookahShisha\Removefreegift\Model\Quote;

use Magento\SalesRule\Model\RuleFactory;
use Amasty\Promo\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\QuoteFactory;

class SalesRule
{
    protected RuleFactory $ruleFactory;

    protected CollectionFactory $collectionFactory;

    protected Session $checkoutSession;

    protected QuoteFactory $quoteFactory;


    /**
     * @param CollectionFactory $collectionFactory
     * @param Session $checkoutSession
     * @param QuoteFactory $quoteFactory
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Session           $checkoutSession,
        QuoteFactory      $quoteFactory,
        RuleFactory       $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @param $quoteId
     * @param $ruleRowId
     * @return bool|void
     */
    public function getSalesRuleIdByQuote($quoteId, $ruleRowId)
    {
        try {
            //Get Rule Id By AmproRule Id
            $rule = $this->ruleFactory->create();
            $ruleId = $this->getRuleIdByRowId($rule, $ruleRowId);

            //Check promo rule id exist
            if (isset($ruleId)) {
                //Get the quote
                $quote = $this->checkoutSession->getQuote();

                //if quote not exist
                if (!isset($quote)) {
                    $quote = $this->quoteFactory->create()->load($quoteId);
                }

                //Check whether the rule id already applied
                if (!$this->isQuoteRuleExist($quote->getAppliedRuleIds(), $ruleId)) {
                    $rule = $rule->load($ruleId);

                    //Get All Quote Items and validate only the non-promo item
                    $isValid = true;
                    foreach ($quote->getAllVisibleItems() as $quoteItem) {
                        $parentId = $quoteItem->getParentItemId();
                        if (!isset($parentId)) {
                            if ($rule->getId()
                                && $rule->getActions()->validate($quoteItem)
                                && $this->isPromoItem($quoteItem->getSku())) {
                                return true;
                            }
                            $isValid = false;
                        }
                    }

                    //To check all the products in the cart
                    return $isValid;
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
        if (!empty($appliedRuleIds)) {
            $appliedRuleIds = explode(',', $appliedRuleIds);
            if (in_array($ruleId, $appliedRuleIds)) {
                return true;
            }
        }
        return false;
    }
}
