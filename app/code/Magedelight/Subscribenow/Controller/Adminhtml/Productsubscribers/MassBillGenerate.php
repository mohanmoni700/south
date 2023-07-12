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
namespace Magedelight\Subscribenow\Controller\Adminhtml\Productsubscribers;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory;
use Magedelight\Subscribenow\Model\Service\OrderServiceFactory;
use Magedelight\Subscribenow\Logger\Logger;
use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;

class MassBillGenerate extends AbstractMassAction
{
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param ProductSubscribersFactory $productSubscribersFactory
     * @param CollectionFactory $collectionFactory
     * @param OrderServiceFactory $orderServiceFactory
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProductSubscribersFactory $productSubscribersFactory,
        CollectionFactory $collectionFactory,
        OrderServiceFactory $orderServiceFactory,
        Logger $logger
    ) {
        $this->logger = $logger;
        parent::__construct($context, $filter, $productSubscribersFactory, $collectionFactory, $orderServiceFactory);
    }
    
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $orderService = $this->orderServiceFactory->create();
        
        $successRow = [];
        $this->logger->info("mass bill generate order started...");
        foreach ($collection as $model) {
            if ($model->getId() && $this->isActive($model)) {
                $subscription = $this->subscriberFactory->create()->load($model->getId());
                try {
                    $orderService->createSubscriptionOrder($subscription, ProductSubscriptionHistory::HISTORY_BY_ADMIN);
                    array_push($successRow, $model->getId());
                } catch (\Exception $ex) {
                }
            }
        }
        
        $records = count($successRow);
        if ($records) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) subscription have been generated.', $records));
        } else {
            $this->messageManager->addErrorMessage(__('Unable to update subscription profile due to invalid profile.'));
        }
        
        $this->logger->info("mass bill generate order end.");
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
