<?php

namespace Alfakher\Webhook\Observer;

use Magento\Framework\Event\Observer;

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
            parent::execute($observer);
        } else {
            $this->updateObserver($observer);
        }
    }
}
