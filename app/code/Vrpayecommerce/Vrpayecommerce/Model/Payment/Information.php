<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Payment;

class Information extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Vrpayecommerce\Vrpayecommerce\Model\ResourceModel\Payment\Information');
    }

    /**
     * check the payment account registered exist
     * @param  array  $parameters
     * @param  string  $registrationId
     * @return boolean
     */
    public function isRegistrationExist($parameters, $registrationId)
    {
        $informationCollection = $this->getCollection()
            ->addFieldToFilter('customer_id', $parameters['customerId'])
            ->addFieldToFilter('server_mode', $parameters['serverMode'])
            ->addFieldToFilter('channel_id', $parameters['channelId'])
            ->addFieldToFilter('registration_id', $registrationId)
            ->addFieldToFilter('payment_group', $parameters['paymentGroup']);

        $data = $informationCollection->getData();
        if (!empty($data)) {
            return true;
        }

        return false;
    }

    /**
     * get payments information
     * @param  array $parameters
     * @return object
     */
    public function getPaymentInformation($parameters)
    {
        $informationCollection = $this->getCollection()
            ->addFieldToFilter('customer_id', $parameters['customerId'])
            ->addFieldToFilter('server_mode', $parameters['serverMode'])
            ->addFieldToFilter('channel_id', $parameters['channelId'])
            ->addFieldToFilter('payment_group', $parameters['paymentGroup']);

        return $informationCollection->getData();
    }

    /**
     * get the payment account registered based on the information id
     * @param  array $parameters
     * @param  int $informationId
     * @return object
     */
    public function getRegistrationByInformationId($parameters, $informationId)
    {
        $informationCollection = $this->getCollection()
            ->addFieldToFilter('information_id', (int)$informationId)
            ->addFieldToFilter('customer_id', $parameters['customerId'])
            ->addFieldToFilter('server_mode',$parameters['serverMode'])
            ->addFieldToFilter('channel_id', $parameters['channelId'])
            ->addFieldToFilter('payment_group', $parameters['paymentGroup'])
            ->setPageSize(1);

        return $informationCollection->getData();
    }

    /**
     * get the payment account registered based on the registration id
     * @param  string $registrationId
     * @return object
     */
    public function getRegistrationByRegistrationId($registrationId)
    {
        $informationCollection = $this->getCollection()
            ->addFieldToFilter('registration_id', $registrationId);

        return $informationCollection->getData();
    }

    /**
     * delete a payment account registered based on id
     * @param  string $informationId
     * @return void
     */
    public function deletePaymentInformationById($informationId)
    {
        $this->load($informationId)->delete();
    }

    /**
     * insert a payment account into the database
     * @param  array $parameters
     * @return void
     */
    public function insertRegistration($parameters)
    {
        $this->setData ('customer_id', $parameters['customerId']);
        $this->setData ('payment_group', $parameters['paymentGroup']);
        $this->setData ('server_mode', $parameters['serverMode']);
        $this->setData ('channel_id', $parameters['channelId']);
        $this->setData ('registration_id', $parameters['registrationId']);
        $this->setData ('brand', $parameters['paymentBrand']);
        $this->setData ('holder', $parameters['holder'] ?? '');
        $this->setData ('email', $parameters['email']);
        $this->setData ('last_4digits', $parameters['last4Digits']);
        $this->setData ('expiry_month', $parameters['expiryMonth']);
        $this->setData ('expiry_year', $parameters['expiryYear']);
        $this->save();
    }

    /**
     * update a payment account into the database
     * @param  array $parameters
     * @param  string $informationId
     * @return void
     */
    public function updateRegistration($parameters, $informationId)
    {
        $this->load($informationId)
            ->setData ('customer_id', $parameters['customerId'])
            ->setData ('payment_group', $parameters['paymentGroup'])
            ->setData ('server_mode', $parameters['serverMode'])
            ->setData ('channel_id', $parameters['channelId'])
            ->setData ('registration_id', $parameters['registrationId'])
            ->setData ('brand', $parameters['paymentBrand'])
            ->setData ('holder', $parameters['holder'])
            ->setData ('email', $parameters['email'])
            ->setData ('last_4digits', $parameters['last4Digits'])
            ->setData ('expiry_month', $parameters['expiryMonth'])
            ->setData ('expiry_year', $parameters['expiryYear'])
            ->save();
    }
}
