<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Service\Payment;

use Magento\Customer\Model\Customer;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Class AbstractPayment
 *
 * Necessary function to implement new payment gateway
 * @package Magedelight\Subscribenow\Model\Service\Payment
 */
abstract class AbstractPayment
{
    const CARD_PREFIX = "XXXX-%s";

    protected $order;
    protected $encryptor;
    protected $objectManager;
    protected $dataObject;
    protected $customer;
    protected $tokenManagement;
    protected $cardCollectionFactory;
    protected $data;

    public function __construct(
        EncryptorInterface $encryptor,
        DataObjectFactory $dataObject,
        ObjectManagerInterface $objectManager,
        Customer $customer,
        PaymentTokenManagementInterface $paymentToken,
        $cardCollectionFactory,
        $order,
        array $data = []
    ) {
        $this->encryptor = $encryptor;
        $this->dataObject = $dataObject;
        $this->objectManager = $objectManager;
        $this->customer = $customer;
        $this->tokenManagement = $paymentToken;
        $this->cardCollectionFactory = $cardCollectionFactory;
        $this->order = $order;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    protected function getPaymentInformation()
    {
        return $this->order->getPayment();
    }

    protected function create($class, $array = [])
    {
        return $this->objectManager->create($class, $array);
    }

    public function getTitle()
    {
        return $this->getPaymentInformation()->getAdditionalInformation('method_title');
    }

    /**
     * @return mixed
     */
    public function getMethodCode()
    {
        return $this->getPaymentInformation()->getMethod();
    }

    public function getImportData()
    {
        /** @var \Magedelight\Subscribenow\Model\ProductSubscribers $subscription */
        $subscription = $this->data['subscription_instance'];
        return [
            'method' => $subscription->getPaymentMethodCode(),
            'subscription_id' => $subscription->getPaymentToken()
        ];
    }

    /**
     * Get Payment Token
     * @return string
     */
    public function getPaymentToken()
    {
        return null;
    }

    /**
     * @param null $key
     * @return mixed
     */
    protected function getAdditionalInformation($key = null)
    {
        if ($key) {
            return $this->getPaymentInformation()->getAdditionalInformation($key);
        }
        return $this->getPaymentInformation()->getAdditionalInformation();
    }

    protected function getCollection($key, $value)
    {
        return $this->cardCollectionFactory->create()->addFieldToFilter($key, $value);
    }
}
