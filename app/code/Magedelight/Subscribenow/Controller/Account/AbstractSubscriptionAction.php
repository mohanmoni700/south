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

namespace Magedelight\Subscribenow\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magento\Customer\Model\Session as CustomerSession;

abstract class AbstractSubscriptionAction extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    /**
     * @var ProductSubscribersFactory
     */
    private $subscribersFactory;
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Resume constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProductSubscribersFactory $subscribersFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductSubscribersFactory $subscribersFactory,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->subscribersFactory = $subscribersFactory;
        $this->customerSession = $customerSession;
    }

    public function validateSubscription($subscription = null)
    {
        if ($this->customerSession->getId() != $subscription->getCustomerId()) {
            throw new LocalizedException(__("Invalid subscription requested"));
        }
        return true;
    }

    public function getSubscription()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->subscribersFactory->create()->load($id);

        return $model;
    }
}
