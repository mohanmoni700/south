<?php

namespace HookahShisha\Removefreegift\Model\SalesRule;

use Amasty\Promo\Model\ResourceModel\Rule\CollectionFactory;

class RuleHelper
{
    protected CollectionFactory $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * To get promo sku
     * @param $rowId
     * @return array|mixed|string|null
     */
    public function getPromoSkus($rowId)
    {
        if (isset($rowId)) {
            return $this->collectionFactory->create()
                ->addFieldToFilter('salesrule_id', ['eq' => $rowId])
                ->getFirstItem()->getData('sku');
        }
        return null;
    }


    /**
     * @param $rule
     * @param $item
     * @return bool
     */
    public function validatePromoItem($rule, $item)
    {
        $action = (string)$rule->getSimpleAction();

        //check for the amasty promo condition
        if (strpos($action, "ampromo_") !== false) {
            $rowId = $rule->getRowId();
            $prompSkus = $this->getPromoSkus($rowId);

            if (isset($prompSkus) && !empty($prompSkus)) {
                $sku = $item->getProduct()->getSku();

                //check whether the sku is promo item
                if (strpos($prompSkus, $sku) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
