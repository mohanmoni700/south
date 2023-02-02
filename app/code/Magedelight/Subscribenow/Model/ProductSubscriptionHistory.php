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

namespace Magedelight\Subscribenow\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ProductSubscriptionHistory extends \Magento\Framework\Model\AbstractModel
{

    const HISTORY_BY_CRON = 0;
    const HISTORY_BY_ADMIN = 1;
    const HISTORY_BY_CUSTOMER = 2;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * ProductSubscriptionHistory constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param TimezoneInterface $timezone
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        TimezoneInterface $timezone,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->timezone = $timezone;
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Subscribenow\Model\ResourceModel\ProductSubscriptionHistory');
    }

    /**
     * @param $subscriptionId
     * @param $modifiedBy
     * @param $comment
     * @return $this
     */
    public function addSubscriptionHistory($subscriptionId, $modifiedBy, $comment)
    {
        $this->setSubscriptionId($subscriptionId)
            ->setModifyBy($modifiedBy)
            ->setComment($comment)
            ->setCreatedAt($this->timezone->date(null, false, false))
            ->save();
        return $this;
    }
}
