<?php

namespace Alfakher\Webhook\Controller\Adminhtml\Edit;

class Shipping extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Shipping
{
    /**
     * @inheritDoc
     */
    protected function update()
    {
        $this->updateShippingMethod();
        /* Start - New event added*/
        $this->_eventManager->dispatch(
            'blueedit_save_after',
            [
                'item' => $this->getOrder()
            ]
        );
        /* end - New event added*/
    }
}
