<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Model\Api;

use Ooka\OokaSerialNumber\Api\SerialNumberRepositoryInterface;
use Ooka\OokaSerialNumber\Model\ResourceModel\SerialNumber as ResourceModel;
use Ooka\OokaSerialNumber\Model\ResourceModel\SerialNumber\Collection;
use Ooka\OokaSerialNumber\Model\ResourceModel\SerialNumber\CollectionFactory;
use Ooka\OokaSerialNumber\Model\SerialNumber as Model;
use Ooka\OokaSerialNumber\Model\SerialNumberFactory as ModelFactory;
use Exception;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;

class SerialNumberRepository implements SerialNumberRepositoryInterface
{
    /**
     * @var ModelFactory
     */
    private $modelFactory;
    /**
     * @var ResourceModel
     */
    private $resourceModel;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessorInterface;
    /**
     * @var SearchResultInterface
     */
    private $searchCriteriaInterface;
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ModelFactory $modelFactory
     * @param ResourceModel $resourceModel
     * @param CollectionProcessorInterface $collectionProcessorInterface
     * @param SearchResultInterface $searchCriteriaInterface
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ModelFactory $modelFactory,
        ResourceModel $resourceModel,
        CollectionProcessorInterface $collectionProcessorInterface,
        SearchResultInterface $searchCriteriaInterface,
        SearchResultFactory $searchResultFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessorInterface = $collectionProcessorInterface;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * Get data by id
     *
     * @param int $id
     * @return Model
     */
    public function getDataBYId($id): Model
    {
        return $this->load($id);
    }

    /**
     * Load Data
     *
     * @param int $value
     * @param string|null $field
     * @return Model
     */
    public function load($value, $field = null): Model
    {
        $model = $this->create();
        $this->resourceModel->load($model, $value, $field);
        return $model;
    }

    /**
     * Create method
     *
     * @return Model
     */
    public function create(): Model
    {
        return $this->modelFactory->create();
    }

    /**
     * Save by model
     *
     * @param Model $model
     * @return Model|void
     * @throws AlreadyExistsException
     */
    public function save(Model $model)
    {
        $this->resourceModel->save($model);
    }

    /**
     * After Save
     *
     * @param Model $model
     * @return Model|void
     */
    public function afterSave(Model $model)
    {
        return $this->afterSave($model);
    }

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById($id): bool
    {
        $model = $this->load($id);
        return $this->delete($model);
    }

    /**
     * Delete method
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        try {
            $this->resourceModel->delete($model);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Deletet by field
     *
     * @param int $value
     * @param string|null $field
     * @return bool
     */
    public function deleteByField($value, $field = null): bool
    {
        $model = $this->load($value, $field);
        return $this->delete($model);
    }

    /**
     * Get collection
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->collectionFactory->create();
    }

    /**
     * Load Subscription data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultFactory->create();
        $collection = $this->collectionFactory->create();
        $this->collectionProcessorInterface->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
