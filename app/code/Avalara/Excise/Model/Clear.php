<?php

namespace Avalara\Excise\Model;

use Avalara\Excise\Framework\Constants;
use Avalara\Excise\Logger\ExciseLogger;
use Avalara\Excise\Model\ResourceModel\Queue\CollectionFactory as QueueCollFactory;
use Avalara\Excise\Model\Queue;
use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use Avalara\Excise\Model\ResourceModel\Log\CollectionFactory as LogCollFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Clear
{
    /**
     * @var ExciseLogger
     */
    protected $logger;

    /**
     * @var QueueCollFactory
     */
    protected $queueCollFactory;

    /**
     * @var LogCollFactory
     */
    protected $logCollFactory;

    /**
     * @var ExciseTaxConfig
     */
    protected $exciseTaxConfig;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param ExciseLogger $logger
     * @param QueueCollFactory $queueCollFactory
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param LogCollFactory $logCollFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        ExciseLogger $logger,
        QueueCollFactory $queueCollFactory,
        ExciseTaxConfig $exciseTaxConfig,
        LogCollFactory $logCollFactory,
        DateTime $dateTime
    ) {
        $this->queueCollFactory = $queueCollFactory;
        $this->logger = $logger;
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->logCollFactory = $logCollFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Initiates the clear logs and queue process
     *
     * @return void
     */
    public function process()
    {
        $this->clearDbLogs();

        $this->clearQueue();
    }

    public function clearDbLogs()
    {
        $limit = $this->exciseTaxConfig->getLogLimit();
        $this->logger->debug(__('Initiating log clearing from cron job'));
        $filteredDate = $this->getFilterDate($limit);
        $logs = $this->logCollFactory->create()
            ->addFieldToFilter('created_at', ['lteq' => $filteredDate]);
        
        $size = 0;
		if(!empty($logs)){
			foreach ($logs as $log) {
				$log->delete();
				$size++;
			}
		}
        $this->logger->debug(
            __('Completed log clearing from cron job. Total Deleted: ' . $size),
            [
                'delete_count' => $size,
                'extra' => [
                    'class' => __METHOD__
                ]
            ]
        );
        return $size;
    }

    public function clearQueue()
    {
        $limit = $this->exciseTaxConfig->getQueueLimit();
        $this->logger->debug(__('Initiating queue clearing from cron job'));
        $filteredDate = $this->getFilterDate($limit);
        $tasks = $this->queueCollFactory->create()
            ->addFieldToFilter('updated_at', ['lteq' => $filteredDate]);
        $size = 0;
		if(!empty($tasks)){
			foreach ($tasks as $task) {
				$task->delete();
				$size++;
			}
		}
        $this->logger->debug(
            __('Completed queue clearing from cron job. Total Deleted: ' . $size),
            [
                'delete_count' => $size,
                'extra' => [
                    'class' => __METHOD__
                ]
            ]
        );
        return $size;
    }

    private function getFilterDate($days)
    {
        return $this->dateTime->gmtDate('Y-m-d', strtotime('-' . $days . ' day'));
    }
}
