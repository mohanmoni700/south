<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Alfakher\MyDocument\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface MyDocumentRepositoryInterface
{

    /**
     * Save MyDocument

     * @param \Alfakher\MyDocument\Api\Data\MyDocumentInterface $myDocument
     * @return \Alfakher\MyDocument\Api\Data\MyDocumentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Alfakher\MyDocument\Api\Data\MyDocumentInterface $myDocument
    );

    /**
     * Retrieve MyDocument

     * @param string $mydocumentId
     * @return \Alfakher\MyDocument\Api\Data\MyDocumentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($mydocumentId);

    /**
     * Retrieve MyDocument matching the specified criteria.

     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Alfakher\MyDocument\Api\Data\MyDocumentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete MyDocument

     * @param \Alfakher\MyDocument\Api\Data\MyDocumentInterface $myDocument
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Alfakher\MyDocument\Api\Data\MyDocumentInterface $myDocument
    );

    /**
     * Delete MyDocument by ID

     * @param string $mydocumentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($mydocumentId);
}
