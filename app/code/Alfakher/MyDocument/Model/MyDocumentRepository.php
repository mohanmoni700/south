<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Alfakher\MyDocument\Model;

use Alfakher\MyDocument\Api\Data\MyDocumentInterface;
use Alfakher\MyDocument\Api\Data\MyDocumentInterfaceFactory;
use Alfakher\MyDocument\Api\Data\MyDocumentSearchResultsInterfaceFactory;
use Alfakher\MyDocument\Api\MyDocumentRepositoryInterface;
use Alfakher\MyDocument\Model\ResourceModel\MyDocument as ResourceMyDocument;
use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory as MyDocumentCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class MyDocumentRepository implements MyDocumentRepositoryInterface
{

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ResourceMyDocument
     */
    protected $resource;

    /**
     * @var MyDocumentInterfaceFactory
     */
    protected $myDocumentFactory;

    /**
     * @var MyDocumentCollectionFactory
     */
    protected $myDocumentCollectionFactory;

    /**
     * @var MyDocument
     */
    protected $searchResultsFactory;

    /**
     * @param ResourceMyDocument $resource
     * @param MyDocumentInterfaceFactory $myDocumentFactory
     * @param MyDocumentCollectionFactory $myDocumentCollectionFactory
     * @param MyDocumentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceMyDocument $resource,
        MyDocumentInterfaceFactory $myDocumentFactory,
        MyDocumentCollectionFactory $myDocumentCollectionFactory,
        MyDocumentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->myDocumentFactory = $myDocumentFactory;
        $this->myDocumentCollectionFactory = $myDocumentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(MyDocumentInterface $myDocument)
    {
        try {
            $this->resource->save($myDocument);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the myDocument: %1',
                $exception->getMessage()
            ));
        }
        return $myDocument;
    }

    /**
     * @inheritDoc
     */
    public function get($myDocumentId)
    {
        $myDocument = $this->myDocumentFactory->create();
        $this->resource->load($myDocument, $myDocumentId);
        if (!$myDocument->getId()) {
            throw new NoSuchEntityException(__('MyDocument with id "%1" does not exist.', $myDocumentId));
        }
        return $myDocument;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->myDocumentCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(MyDocumentInterface $myDocument)
    {
        try {
            $myDocumentModel = $this->myDocumentFactory->create();
            $this->resource->load($myDocumentModel, $myDocument->getMydocumentId());
            $this->resource->delete($myDocumentModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the MyDocument: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($myDocumentId)
    {
        return $this->delete($this->get($myDocumentId));
    }
}
