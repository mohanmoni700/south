<?php

namespace Alfakher\Webhook\Observer;

use Magento\Framework\Event\Observer;
use Exception;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Webhook\Model\CronScheduleFactory;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Helper\Data;
use Magento\Framework\Registry;

class AfterRmaCreate extends AfterSave
{
    /**
     * Document add type
     * @var string
     */
    protected $hookType = 'create_rma';
    /**
     * Document Update type
     * @var string
     */
    protected $hookTypeUpdate = 'update_rma';

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
        Registry $registry
    ) {
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
     * Default Method
     *
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        if ($event->getName() === "rma_create_after") {
            $registeryObserverCheck = $this->registry->registry('newrmawebhooktrigger');
            if (!$registeryObserverCheck) {
                $this->registry->register('newrmawebhooktrigger', true);
                parent::execute($observer);
            }
        } else {
            $this->updateObserver($observer);
        }
    }
}
