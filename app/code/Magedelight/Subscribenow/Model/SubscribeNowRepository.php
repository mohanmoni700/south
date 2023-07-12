<?php

namespace Magedelight\Subscribenow\Model;

use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterfaceFactory;
use Magedelight\Subscribenow\Api\Data\ProductSubscribersSearchResultsInterface;
use Magedelight\Subscribenow\Api\ProductSubscribersRepositoryInterface;
use Magedelight\Subscribenow\Api\SubscribeNowRepositoryInterface;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers as ResourceProductSubscribers;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;

class SubscribeNowRepository implements SubscribeNowRepositoryInterface
{
    /** @var array */
    private $instances = [];

    /** @var ResourceProductSubscribers */
    private $resource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * @var ProductSubscribersRepositoryInterface
     */
    protected $productSubscribersRepository;

    /** @var ProductSubscribersInterfaceFactory */
    private $subscriberInterfaceFactory;

    /**
     * @var Request
     */
    protected $_request;

    protected $filterBuilder;

    protected $sortOrderBuilder;

    protected $filterGroupBuilder;

    public function __construct(
        ResourceProductSubscribers $resource,
        SearchCriteriaBuilder $searchCriteria,
        ProductSubscribersRepositoryInterface $productSubscribersRepository,
        ProductSubscribersInterfaceFactory $subscriptionInterfaceFactory,
        Request $request,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->resource = $resource;
        $this->searchCriteria = $searchCriteria;
        $this->productSubscribersRepository = $productSubscribersRepository;
        $this->subscriberInterfaceFactory = $subscriptionInterfaceFactory;
        $this->_request = $request;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @return array
     */
    public function getPostParams()
    {
        $postParams = $this->_request->getBodyParams();
        if (empty($postParams)) {
            $postParams = $this->_request->getParams();
        }
        return $postParams;
    }

    /**
     * @param $postParams
     * @param $key
     * @return string
     */
    protected function getParamsByKey($postParams, $key)
    {
        $filterParams = '';
        if (!empty($postParams[$key])) {
            $filterParams = $postParams[$key];
        } elseif (!empty($postParams) && !empty($postParams['searchCriteria']) && !empty($postParams['searchCriteria'][$key])) {
            $filterParams = $postParams['searchCriteria'][$key];
        }
        return $filterParams;
    }

    /**
     * Retrieve Subscription matching the specified criteria.
     * @param int $customerId
     * @param mixed|null $postParams
     * @return ProductSubscribersSearchResultsInterface
     * @throws LocalizedException
     */
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
                foreach ($filterValue as $filterCondition => $value) {
                    $conditionCode = $filterCondition;
                    if (is_array($value)) {
                        $conditionValue = implode(', ', $value);
                    } else {
                        $conditionValue = $value;
                    }
                }
                $attr = $this->filterBuilder->setField($field)
                    ->setConditionType($conditionCode)
                    ->setValue($conditionValue)
                    ->create();
                $filterArray[] = $this->filterGroupBuilder->addFilter($attr)->create();
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

    /**
     * Retrieve ProductSubscribers.
     *
     * @param int $subscriptionId
     * @param int $customerId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($subscriptionId, $customerId)
    {
        if (!isset($this->instances[$subscriptionId])) {
            /** @var \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface|\Magento\Framework\Model\AbstractModel $subscription */
            $subscription = $this->subscriberInterfaceFactory->create();
            $this->resource->load($subscription, $subscriptionId);
            if (!$subscription->getId()) {
                throw new NoSuchEntityException(__('Requested Subscription id doesn\'t exist'));
            }

            $this->validateSubscriptionAPIAccess($subscription, $customerId);

            $this->instances[$subscriptionId] = $subscription;
        }
        return $this->instances[$subscriptionId];
    }

    public function validateSubscriptionAPIAccess($subscription, $customerId)
    {
        if ($subscription->getCustomerId() != $customerId) {
            throw new NoSuchEntityException(__('Requested Subscription doesn\'t exist for this customer'));
        }
        return true;
    }
}
