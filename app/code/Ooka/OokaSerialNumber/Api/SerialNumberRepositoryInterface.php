<?php

declare (strict_types=1);

namespace Ooka\OokaSerialNumber\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Ooka\OokaSerialNumber\Api\Data\SerialNumberInterface;
use Ooka\OokaSerialNumber\Model\ResourceModel\SerialNumber\Collection;
use Ooka\OokaSerialNumber\Model\SerialNumber as Model;

interface SerialNumberRepositoryInterface
{
    /**
     * Get data by id
     *
     * @param int $id
     * @return Model
     */
    public function getDataBYId($id);

    /**
     * Save model
     *
     * @param Model $model
     * @return Model
     */
    public function save(Model $model);

    /**
     * After save model
     *
     * @param Model $model
     * @return Model
     */
    public function afterSave(Model $model);

    /**
     * Delete model
     *
     * @param Model $model
     * @return Model
     */
    public function delete(Model $model);

    /**
     * Load data
     *
     * @param int $value
     * @param bool $field
     * @return Model
     */
    public function load($value, $field = null);

    /**
     * Create method for model
     *
     * @return Model $model
     */
    public function create();

    /**
     * Delete by id
     *
     * @param int $id
     * @return Model
     */
    public function deleteById($id);

    /**
     * Get collection
     *
     * @return Collection
     */
    public function getCollection();

    /**
     * Delete by field
     *
     * @param int $value
     * @param bool $field
     * @return mixed
     */
    public function deleteByField($value, $field = null);

    /**
     * Get list of item
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SerialNumberInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
