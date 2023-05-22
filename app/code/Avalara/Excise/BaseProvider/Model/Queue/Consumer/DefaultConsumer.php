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
namespace Avalara\Excise\BaseProvider\Model\Queue\Consumer;

use Psr\Log\LoggerInterface;
use Avalara\Excise\BaseProvider\Helper\Config as QueueConfig;
use Avalara\Excise\BaseProvider\Model\ResourceModel\Queue\CollectionFactory as QueueCollFactory;

abstract class DefaultConsumer
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var QueueCollFactory
     */
    protected $queueCollFactory;

    /**
     * @var QueueConfig
     */
    protected $queueConfig;

    /**
     * @var string
     */
    protected $client = 'default';

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @param LoggerInterface 
     * @param QueueConfig $queueConfig
     * @param QueueCollFactory $queueCollFactory
     */
    public function __construct(
        LoggerInterface $logger,
        QueueConfig $queueConfig,
        QueueCollFactory $queueCollFactory
    ) {
        $this->logger = $logger;
        $this->queueConfig = $queueConfig;
        $this->queueCollFactory = $queueCollFactory;
        $this->batchSize = $this->queueConfig->getBatchSize();
    }

    /**
     * @param string $client
     * @return \Avalara\Excise\BaseProvider\Model\ResourceModel\Queue\Collection
     */
    public function getJobs($client = '')
    {
        if ($client == '') {
            $client = $this->client;
        }
        return $this->queueCollFactory->create()
            ->addFieldToFilter('client', ['eq' => $client])
            ->setCurPage(1)
            ->setPageSize($this->batchSize);
    }

    /**
     * @param \Avalara\Excise\BaseProvider\Api\Data\QueueInterface $queueJob
     * @return array
     */
    public abstract function consume(\Avalara\Excise\BaseProvider\Api\Data\QueueInterface $queueJob);
}
