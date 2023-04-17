<?php
/*
 * Avalara_BaseProvider
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright Copyright (c) 2021 Avalara, Inc
 * @license    http: //opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Avalara\Excise\BaseProvider\Api;

/**
 * Queue Job CRUD interface.
 * @api
 */
interface QueueRepositoryInterface
{
    /**
     * Save queue job.
     *
     * @param \Avalara\Excise\BaseProvider\Api\Data\QueueInterface $queueJob
     * @return \Avalara\Excise\BaseProvider\Api\Data\QueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #public function save(\Avalara\Excise\BaseProvider\Api\Data\QueueInterface $queueJob);

    /**
     * Retrieve Queue job.
     *
     * @param int $jobId
     * @return \Avalara\Excise\BaseProvider\Api\Data\QueueInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #public function getById($jobId);

    /**
     * Retrieve Queue jobs matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Avalara\Excise\BaseProvider\Api\QueueSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
