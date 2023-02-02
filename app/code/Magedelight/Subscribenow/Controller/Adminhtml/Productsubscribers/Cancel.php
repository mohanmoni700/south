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

use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;

class Cancel extends AbstractSubscription
{
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        $model = $this->subscriberFactory->create()->load($id);
        try {
            $model->cancelSubscription(ProductSubscriptionHistory::HISTORY_BY_ADMIN);
            $this->messageManager->addSuccessMessage(__('Subscription profile has been successfully updated.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update subscription profile information.'));
        }
        return $resultRedirect->setPath('*/*/view', ['id' => $id]);
    }
}
