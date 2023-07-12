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
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;

/**
 * Abstract Subscription Class
 */
abstract class AbstractSubscription extends Action
{

    const SUBSCRIPTION_REGISTRY = 'md_subscribenow_product_subscriber';
    
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
    
    /**
     * @var Registry
     */
    public $coreRegistry;
    
    /**
     * @var ProductSubscribersFactory
     */
    public $subscriberFactory;
    
    /**
     * Constructor
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        ProductSubscribersFactory $subscriberFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->subscriberFactory = $subscriberFactory;
        
        parent::__construct($context);
    }
    
    public function initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->setActiveMenu('Magedelight_Subscribenow::subscribenow_productsubscription');
        $resultPage->addBreadcrumb(__('Magedelight'), __('Magedelight'));
        $resultPage->addBreadcrumb(__('Manage Subscription'), __('Manage Subscription'));

        return $resultPage;
    }
    
    /**
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    public function getRegistry()
    {
        return $this->coreRegistry->registry(self::SUBSCRIPTION_REGISTRY);
    }
    
    /**
     * Load Model Data
     * @return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    public function init()
    {
        $id = $this->getRequest()->getParam('id');
        $subscriptionModel = $this->subscriberFactory->create()->load($id);
        
        if (!$subscriptionModel->getId()) {
            $this->messageManager->addErrorMessage(__('Subscription profile is not exist.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

        $this->coreRegistry->register(self::SUBSCRIPTION_REGISTRY, $subscriptionModel);
        return $subscriptionModel;
    }
    
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Subscribenow::subscribenow_subscription');
    }
}
