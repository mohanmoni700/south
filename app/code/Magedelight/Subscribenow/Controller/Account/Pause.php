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

class Pause extends AbstractSubscriptionAction
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
                $model->pauseSubscription();
                $this->messageManager->addSuccessMessage(__('Subscription profile has been successfully paused.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addWarningMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to update subscription profile information.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Subscription profile does not exist.'));
        }

        return $resultRedirect->setPath('*/*/summary', ['_current' => true]);
    }
}
