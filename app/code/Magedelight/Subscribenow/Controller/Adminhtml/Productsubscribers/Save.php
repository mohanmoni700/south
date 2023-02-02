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

use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;

class Save extends AbstractSubscription
{
    /**
     * Save subscription profile
     * @return \Magento\Framework\Controller\ResultInterface
     */
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('subscription_id');
        $model = $this->subscriberFactory->create()->load($id);
        try {
            $model->updateSubscription($this->getRequest()->getParams(), ProductSubscriptionHistory::HISTORY_BY_ADMIN);
            $this->messageManager->addSuccessMessage(__('Subscription profile has been successfully updated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Item is not updating'));
        }
        return $resultRedirect->setPath('*/*/view', ['id' => $id]);
    }
}
