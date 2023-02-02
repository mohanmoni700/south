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
namespace Magedelight\Subscribenow\Block\Customer\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Link extends Template
{

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var SubscribeHelper
     */
    private $subscriptionHelper;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Button constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscribeHelper $subscriptionHelper
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscribeHelper $subscriptionHelper,
        TimezoneInterface $timezone,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->timezone = $timezone;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [
            'view' => [
                'title' => 'Profile Information',
                'url' => $this->getProfileUrl()
            ],
            'order' => [
                'title' => 'Related Orders',
                'url' => $this->getRelatedOrderUrl(),
            ],
            'history' => [
                'title' => 'Profile History',
                'url' => $this->getHistoryUrl()
            ],
        ];
    }

    /**
     * @param $page
     * @return bool
     */
    public function isCurrentPage($page)
    {
        $actionName = $this->getRequest()->getActionName();
        return $page == $actionName;
    }

    /**
     * @return string
     */
    public function getProfileUrl()
    {
        return $this->getUrl('*/*/view', ['id' => $this->getSubscriptionId()]);
    }

    /**
     * @return string
     */
    public function getRelatedOrderUrl()
    {
        return $this->getUrl('*/*/order', ['id' => $this->getSubscriptionId()]);
    }

    /**
     * @return string
     */
    public function getHistoryUrl()
    {
        return $this->getUrl('*/*/history', ['id' => $this->getSubscriptionId()]);
    }

    /**
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    public function getSubscriptionId()
    {
        return $this->getRequest()->getParam('id');
    }
}
