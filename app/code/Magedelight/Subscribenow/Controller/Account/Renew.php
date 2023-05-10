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

use Magento\Framework\Exception\LocalizedException;
use Magedelight\Subscribenow\Model\ProductSubscriptionHistory;

/**
 * Class Renew
 *
 * @since 200.5.0
 * @package Magedelight\Subscribenow\Controller\Account
 */
class Renew extends AbstractSubscriptionAction
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultRedirect = $this->resultRedirectFactory->create();
        $model = $this->getSubscription();

        if ($model->getId()) {
            try {
                $this->validateSubscription($model);
                $subscription = $model->renewSubscription(ProductSubscriptionHistory::HISTORY_BY_CUSTOMER);
                $this->messageManager->addSuccessMessage(__('New #%1 Subscription profile has created successfully updated.', $subscription->getProfileId()));
            } catch (LocalizedException $e) {
                $this->messageManager->addWarningMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to renew subscription.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Subscription does not exist.'));
        }

        return $resultRedirect->setPath('*/*/summary', ['_current' => true]);
    }
}
