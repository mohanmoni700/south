<?php

namespace Alfakher\Webhook\Controller\Adminhtml\Edit;

class Address extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Address
{
    /**
     * @inheritDoc
     */
    protected function update()
    {
        $addressId   = $this->getAddressId();
        $addressData = $this->getAddressData();

        $this->address->loadAddress($addressId);
        $this->address->updateAddress($addressData);

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
