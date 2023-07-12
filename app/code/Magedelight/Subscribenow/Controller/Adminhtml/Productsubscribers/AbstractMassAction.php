<?php

/**
 *  Magedelight
 *  Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Controller\Adminhtml\Productsubscribers;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magedelight\Subscribenow\Model\Service\OrderServiceFactory;

/**
 * Class AbstractMassAction
 */
abstract class AbstractMassAction extends Action
{

    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var CollectionFactory
     */
    public $collectionFactory;
    
    /**
     * @var ProductSubscribersFactory
     */
    public $subscriberFactory;
    
    /**
     * @var OrderServiceFactory
     */
    public $orderServiceFactory;
    
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param ProductSubscribersFactory $productSubscribersFactory
     * @param CollectionFactory $collectionFactory
     * @param OrderServiceFactory $orderServiceFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProductSubscribersFactory $productSubscribersFactory,
        CollectionFactory $collectionFactory,
        OrderServiceFactory $orderServiceFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->subscriberFactory = $productSubscribersFactory;
        $this->orderServiceFactory = $orderServiceFactory;
        parent::__construct($context);
    }

    /**
     * Is Subscription Status Active
     * @param Magedelight\Subscribenow\Model\ProductSubscribers $subscription
     * @return boolean
     */
    public function isActive($subscription)
    {
        return (bool) ($subscription->getSubscriptionStatus() == ProfileStatus::ACTIVE_STATUS);
    }
    
    /**
     * Is Subscription Status Active
     * @param Magedelight\Subscribenow\Model\ProductSubscribers $subscription
     * @return boolean
     */
    public function isPause($subscription)
    {
        return (bool) ($subscription->getSubscriptionStatus() == ProfileStatus::PAUSE_STATUS);
    }
}
