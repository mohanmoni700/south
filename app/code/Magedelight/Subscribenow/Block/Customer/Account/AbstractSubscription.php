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
use Magedelight\Subscribenow\Model\Source\ProfileStatus;

abstract class AbstractSubscription extends Template
{

    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var SubscribeHelper
     */
    protected $subscribeHelper;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

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
        $this->subscribeHelper = $subscriptionHelper;
        $this->timezone = $timezone;
    }

    /**
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    public function getSubscription()
    {
        return $this->registry->registry('current_profile');
    }
}
