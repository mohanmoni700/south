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

namespace Magedelight\Subscribenow\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;

class ProductSubscribers extends AbstractDb
{

    /**
     * @var TimezoneInterface
     */
    private $timezone;
    
    /**
     * @var SubscriptionHelper
     */
    private $helper;

    /**
     * Serializable fields declaration
     * @var array
     */
    protected $_serializableFields = [
        'subscription_item_info' => [null, []],
        'additional_info' => [null, []],
        'order_info' => [null, []],
        'order_item_info' => [null, []],
        'billing_address_info' => [null, []],
        'shipping_address_info' => [null, []],
    ];

    /**
     * ProductSubscribers constructor.
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        SubscriptionHelper $helper,
        TimezoneInterface $timezone,
        $connectionName = null
    ) {
        if (!$connectionName) {
            $connectionName = 'sales';
        }
        
        parent::__construct($context, $connectionName);
        $this->helper = $helper;
        $this->timezone = $timezone;
    }

    /**
     * Model Initialization.
     */
    protected function _construct()
    {
        $this->_init('md_subscribenow_product_subscribers', 'subscription_id');
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->checkFailedThresold($object);
        $dateTime = $this->timezone->date(null, null, false)->format('Y-m-d H:i:s');
        $object->setUpdatedAt($dateTime);
        if ($object->isObjectNew()) {
            $object->setCreatedAt($dateTime);
        }
        return parent::_beforeSave($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->hasData('order_increment_id')) {
            $this->saveOrderRelation($object);
        }
        return parent::_afterSave($object);
    }

    private function saveOrderRelation($object)
    {
        $dataExists = $this->checkIfDataExists($object);
        if (empty($dataExists)) {
            $table = $this->getTable('md_subscribenow_product_associated_orders');
            $data = ['subscription_id' => $object->getId(), 'order_id' => $object->getOrderIncrementId()];
            $this->getConnection()->insert($table, $data);
        }
    }

    private function checkIfDataExists($object)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable('md_subscribenow_product_associated_orders'),
            'relation_id'
        )->where(
            'subscription_id = ?',
            (int) $object->getId()
        )->where(
            'order_id = ?',
            $object->getOrderIncrementId()
        );
        return $adapter->fetchCol($select);
    }
    
    private function checkFailedThresold(&$object)
    {
        if ($object->getSuspensionThreshold()
            && $this->helper->getMaxFailedAllowedTimes()
            && $object->getSuspensionThreshold() >= $this->helper->getMaxFailedAllowedTimes()
            && $object->getSubscriptionStatus() != ProfileStatus::SUSPENDED_STATUS
        ) {
            $object->suspendSubscription($object->getModifiedBy());
            $object->setSubscriptionStatus(ProfileStatus::SUSPENDED_STATUS);
        }
    }
}
