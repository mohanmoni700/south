<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright    2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Class MediaGalleryRepository
 *
 * @package MageModule\Core\Model
 */
class MediaGalleryRepository implements \MageModule\Core\Api\MediaGalleryRepositoryInterface
{
    /**
     * @var \MageModule\Core\Model\MediaGalleryFactory
     */
    private $objectFactory;

    /**
     * @var \MageModule\Core\Model\ResourceModel\MediaGallery
     */
    private $resource;

    /**
     * @var \MageModule\Core\Model\ResourceModel\MediaGallery\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * MediaGalleryRepository constructor.
     *
     * @param MediaGalleryFactory                                                $objectFactory
     * @param ResourceModel\MediaGallery                                         $resource
     * @param ResourceModel\MediaGallery\CollectionFactory                       $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory               $searchResultFactory
     */
    public function __construct(
        \MageModule\Core\Model\MediaGalleryFactory $objectFactory,
        \MageModule\Core\Model\ResourceModel\MediaGallery $resource,
        \MageModule\Core\Model\ResourceModel\MediaGallery\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultFactory
    ) {
        $this->objectFactory       = $objectFactory;
        $this->resource            = $resource;
        $this->collectionFactory   = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @param int $id
     *
     * @return \MageModule\Core\Api\Data\MediaGalleryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        /** @var \MageModule\Core\Api\Data\MediaGalleryInterface|AbstractModel $object */
        $object = $this->objectFactory->create();

        $this->resource->load($object, $id);
        if (!$object->getEntityId()) {
            throw new NoSuchEntityException(__('Media with id "%1" does not exist.', $id));
        }

        return $object;
    }

    /**
     * @param \MageModule\Core\Api\Data\MediaGalleryInterface $object
     *
     * @return \MageModule\Core\Api\Data\MediaGalleryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\MageModule\Core\Api\Data\MediaGalleryInterface $object)
    {
        try {
            $this->resource->save($object);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $object;
    }

    /**
     * @param \MageModule\Core\Api\Data\MediaGalleryInterface $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\MageModule\Core\Api\Data\MediaGalleryInterface $object)
    {
        try {
            $this->resource->delete($object);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }

    /**
     * @param int $entityId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByEntityId($entityId)
    {
        $ids = $this->resource->getAllIdsByEntityId($entityId);
        foreach ($ids as $id) {
            $this->deleteById($id);
        }

        return true;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \MageModule\Core\Model\ResourceModel\MediaGallery\Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var \Magento\Framework\Api\SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
