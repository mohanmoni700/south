<?php

namespace Alfakher\Webhook\Observer;

use Magento\Framework\Event\Observer;
use Mageplaza\Webhook\Model\Config\Source\HookType;
    
class AfterDocumentSave extends AfterSave
{
    /**
     * Document add type
     * @var string
     */
    protected $hookType = 'new_document';
    /**
     * Document Update type
     * @var string
     */
    protected $hookTypeUpdate = 'update_document';

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
        if ($event->getName() == "document_save_after") {
            parent::execute($observer);
        } else {
            $this->updateObserver($observer);
        }
    }
}
