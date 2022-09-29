<?php

namespace Alfakher\Webhook\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Webhook\Helper\Data;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Config\Source\Schedule;
use Mageplaza\Webhook\Model\CronScheduleFactory;
use Mageplaza\Webhook\Model\HookFactory;

class AfterOrderObserver extends \Mageplaza\Webhook\Observer\AfterSave
{
    /**
     * AfterSave constructor.
     *
     * @param HookFactory $hookFactory
     * @param CronScheduleFactory $cronScheduleFactory
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param Registry $registry
     */
    public function __construct(
        HookFactory $hookFactory,
        CronScheduleFactory $cronScheduleFactory,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->hookFactory = $hookFactory;
        $this->helper = $helper;
        $this->scheduleFactory = $cronScheduleFactory;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        parent::__construct(
            $hookFactory,
            $cronScheduleFactory,
            $messageManager,
            $storeManager,
            $helper
        );
    }

    /**
     * Hook Type For Order
     *
     * @var string
     */
    protected $hookType = HookType::ORDER;

    /**
     * Default Execute Method
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getDataObject();
        $orderStatus = [];
        $orderStatus = $this->registry->registry('orderStatus');
        if (!$orderStatus) {
            $orderStatus[] = 'status options';
        }

        if (!in_array($item->getStatus(), $orderStatus)) {
            $orderStatus[] = $item->getStatus();
            if ($this->registry->registry('orderStatus')) {
                $this->registry->unregister('orderStatus');
            }
            $this->registry->register('orderStatus', $orderStatus);

            $schedule = $this->helper->getCronSchedule();
            if ($schedule !== Schedule::DISABLE && $schedule !== null) {
                $hookCollection = $this->hookFactory->create()->getCollection()
                    ->addFieldToFilter('hook_type', $this->hookType)
                    ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('store_ids', [
                        ['finset' => Store::DEFAULT_STORE_ID],
                        ['finset' => $this->helper->getItemStore($item)],
                    ])
                    ->setOrder('priority', 'ASC');
                if ($hookCollection->getSize() > 0) {
                    $schedule = $this->scheduleFactory->create();
                    $data = [
                        'hook_type' => $this->hookType,
                        'event_id' => $item->getId(),
                        'status' => '0',
                    ];

                    try {
                        $schedule->addData($data);
                        $schedule->save();
                    } catch (Exception $exception) {
                        $this->messageManager->addError($exception->getMessage());
                    }
                }
            } else {
                $this->helper->send($item, $this->hookType);
            }
        }
    }
}
