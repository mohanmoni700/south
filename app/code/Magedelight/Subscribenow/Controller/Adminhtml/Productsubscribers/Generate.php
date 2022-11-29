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

use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magedelight\Subscribenow\Model\Service\OrderService;
use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;

class Generate extends AbstractSubscription
{

    /**
     * @var OrderService
     */
    private $orderService;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        ProductSubscribersFactory $subscriberFactory,
        OrderService $orderService
    ) {
    
        parent::__construct($context, $resultPageFactory, $coreRegistry, $subscriberFactory);
        $this->orderService = $orderService;
    }

    /**
     * View subscription profile
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $subscription  =  $this->init();
        
        try {
            $this->orderService->createSubscriptionOrder($subscription, ProductSubscriptionHistory::HISTORY_BY_ADMIN);
            $comment = __(__('Subscription order #%1 created successfully', $subscription->getOrderIncrementId()));
            $this->messageManager->addSuccessMessage($comment);
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/view', ['id' => $subscription->getId(), '_current' => true]);
    }
}
