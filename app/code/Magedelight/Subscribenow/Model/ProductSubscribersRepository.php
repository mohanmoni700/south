<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Subscribenow\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\Subscribenow\Api\ProductSubscribersRepositoryInterface;
use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface;
use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterfaceFactory;
use Magedelight\Subscribenow\Api\Data\ProductSubscribersSearchResultsInterfaceFactory;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers as ResourceProductSubscribers;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\Collection;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory as ProductSubscribersCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory;
use Magedelight\Subscribenow\Model\Service\SubscriptionServiceFactory;

class ProductSubscribersRepository implements ProductSubscribersRepositoryInterface
{
    /** @var array */
    private $instances = [];

    /** @var ResourceProductSubscribers */
    private $resource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ProductSubscribersCollectionFactory */
    private $subscriberCollectionFactory;

    /** @var \Magedelight\Subscribenow\Api\Data\ProductSubscribersSearchResultsInterfaceFactory */
    private $searchResultsFactory;

    /** @var ProductSubscribersInterfaceFactory */
    private $subscriberInterfaceFactory;

    /** @var DataObjectHelper*/
    private $dataObjectHelper;

    /** @var OrderRepositoryInterfaceFactory */
    private $orderRepositoryFactory;

    /** @var SubscriptionServiceFactory */
    private $subscriptionServiceFactory;

    public function __construct(
        ResourceProductSubscribers $resource,
        StoreManagerInterface $storeManager,
        ProductSubscribersCollectionFactory $subscriptionCollectionFactory,
        ProductSubscribersSearchResultsInterfaceFactory $subscriptionSearchResultsInterfaceFactory,
        ProductSubscribersInterfaceFactory $subscriptionInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        OrderRepositoryInterfaceFactory $orderRepository,
        SubscriptionServiceFactory $subscriptionService
    ) {
        $this->resource                 = $resource;
        $this->storeManager             = $storeManager;
        $this->subscriberCollectionFactory  = $subscriptionCollectionFactory;
        $this->searchResultsFactory     = $subscriptionSearchResultsInterfaceFactory;
        $this->subscriberInterfaceFactory   = $subscriptionInterfaceFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->orderRepositoryFactory = $orderRepository;
        $this->subscriptionServiceFactory = $subscriptionService;
    }
    /**
     * Save page.
     *
     * @param \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $subscription
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $subscription)
    {
        try {
            $this->resource->save($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Subscription: %1',
                $exception->getMessage()
            ));
        }
        return $subscription;
    }

    /**
     * Retrieve ProductSubscribers.
     *
     * @param int $subscriptionId
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($subscriptionId)
    {
        if (!isset($this->instances[$subscriptionId])) {
            /** @var \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface|\Magento\Framework\Model\AbstractModel $subscription */
            $subscription = $this->subscriberInterfaceFactory->create();
            $this->resource->load($subscription, $subscriptionId);
            if (!$subscription->getId()) {
                throw new NoSuchEntityException(__('Requested Subscription id doesn\'t exist'));
            }
            $this->instances[$subscriptionId] = $subscription;
        }
        return $this->instances[$subscriptionId];
    }

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magedelight\Subscribenow\Api\Data\ProductSubscribersSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        
        /** @var \Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\Collection $collection */
        $collection = $this->subscriberCollectionFactory->create();
        
        //Add filters from root filter group to the collection
        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // set a default sorting order since this method is used constantly in many
            // different blocks
            $field = 'subscription_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface[] $subscriptions */
        $subscriptions = [];
        /** @var \Magedelight\Subscribenow\Model\ProductSubscribers $subscription */
        foreach ($collection as $subscription) {
            /** @var \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $subscriptionDataObject */
            $subscriptionDataObject = $this->subscriberInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray($subscriptionDataObject, $subscription->getData(), ProductSubscribersInterface::class);
            $subscriptions[] = $subscriptionDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($subscriptions);
        return $searchResults;
    }

    /**
     * Delete Subscription.
     *
     * @param \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $subscription
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface $subscription)
    {
        /** @var \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface|\Magento\Framework\Model\AbstractModel $subscription */
        $id = $subscription->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($subscription);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove subscription %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Subscription by ID.
     *
     * @param int $subscriptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($subscriptionId)
    {
        $subscription = $this->getById($subscriptionId);
        return $this->delete($subscription);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createByOrderId($orderId)
    {
        $order = $this->orderRepositoryFactory->create()->get($orderId);
        $status = $this->subscriptionServiceFactory->create()->create($order);

        $message = $status ?
            __('Subscription Profile(s) created.')
            : __('Something went to wrong on subscription creation time') ;

        return [['success' => $status,'message' => $message]];
    }
}
