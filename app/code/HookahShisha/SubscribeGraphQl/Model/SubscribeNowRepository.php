<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model;

class SubscribeNowRepository extends \Magedelight\Subscribenow\Model\SubscribeNowRepository
{

    public function getSubscriptionsByCustomer($customerId, $postParams = null)
    {
        if (!$postParams) {
            $postParams = $this->getPostParams();
        }
        $currentPage = $sortParams = $pageSize = '';
        $filterParams = [];
        if ($postParams) {
            $filterParams = $this->getParamsByKey($postParams, 'filter');
            $sortParams = $this->getParamsByKey($postParams, 'sort');
            $currentPage = $this->getParamsByKey($postParams, 'currentPage');
            $pageSize = $this->getParamsByKey($postParams, 'pageSize');
        }
        $attr = $this->filterBuilder->setField('customer_id')
            ->setConditionType('eq')
            ->setValue($customerId)
            ->create();
        $filterArray[] = $this->filterGroupBuilder->addFilter($attr)->create();
        if (!empty($filterParams)) {
            foreach ($filterParams as $field => $filterValue) {
                $conditionCode = null;
                $conditionValue = null;
                foreach ($filterValue as $filterCondition => $value) {
                    $conditionCode = $filterCondition;
                    if (is_array($value)) {
                        $conditionValue = implode(', ', $value);
                    } else {
                        $conditionValue = $value;
                    }
                }
                if ($conditionCode && $conditionValue) {
                    $attr = $this->filterBuilder->setField($field)
                        ->setConditionType($conditionCode)
                        ->setValue($conditionValue)
                        ->create();
                }
                $filterArray[] = $this->filterGroupBuilder->addFilter($attr)->create();
            }
        }

        if (is_array($sortParams)) {
            foreach ($sortParams as $item) {
                $sortOrder = $this->sortOrderBuilder->setField($item['field'])
                    ->setDirection($item['direction'])
                    ->create();
                $this->searchCriteria->addSortOrder($sortOrder);
            }
        }

        $subscriptionsList = $this->searchCriteria->setFilterGroups($filterArray)->create();
        if ($currentPage) {
            $subscriptionsList->setCurrentPage($currentPage);
        }
        if ($pageSize) {
            $subscriptionsList->setPageSize($pageSize);
        }
        $items = $this->productSubscribersRepository->getList($subscriptionsList);
        return $items;
    }
}
