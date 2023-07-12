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

class Pause extends AbstractSubscription
{
    
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->init();
        $model = $this->getRegistry();
        
        try {
            $model->pauseSubscription(ProductSubscriptionHistory::HISTORY_BY_ADMIN);
            $this->messageManager->addSuccessMessage(__('Subscription profile has been successfully updated.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to update subscription profile information.'));
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/view', ['id' => $model->getId()]);
    }
}
