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
use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;

class MassCancel extends AbstractMassAction
{
    
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $successRow = [];
        foreach ($collection as $model) {
            if ($model->getId() && $this->isActive($model)) {
                $subscriptionModel = $this->subscriberFactory->create()->load($model->getId());
                $subscriptionModel->cancelSubscription(ProductSubscriptionHistory::HISTORY_BY_ADMIN);
                array_push($successRow, $model->getId());
            }
        }

        $records = count($successRow);
        if ($records) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been cancelled subscription.', $records));
        } else {
            $this->messageManager->addErrorMessage(__('Unable to update subscription profile due to invalid profile.'));
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
