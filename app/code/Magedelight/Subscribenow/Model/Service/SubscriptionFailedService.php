<?php
/**
 * MageDelight Solution Pvt. Ltd.
 *
 * @category MageDelight
 * @package  Magedelight_NHSCustomization
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GPL 3.0
 * @link     http://www.magedelight.com/
 */

namespace Magedelight\Subscribenow\Model\Service;

use Magedelight\Subscribenow\Helper\Data;
use Magedelight\Subscribenow\Logger\Logger;
use Magedelight\Subscribenow\Model\Service\Order\Generate;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class SubscriptionFailedService
 *
 * @since 200.5.0
 * @package Magedelight\Subscribenow\Model\Service
 */
class SubscriptionFailedService
{
    private $helper;
    private $timezone;
    private $logger;
    private $generateOrder;

    public function __construct(
        Data $helper,
        EmailServiceFactory $emailService,
        TimezoneInterface $timezone,
        Logger $logger,
        Generate $generateOrder
    ) {
        $this->helper = $helper;
        $this->emailService = $emailService;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->generateOrder = $generateOrder;
    }

    private function logInfo($message = null)
    {
        if ($message) {
            $this->logger->info($message);
        }
    }

    public function update($subscription, $exception, $message, $modifiedBy = 0)
    {
        if (!$this->helper->isModuleEnable()) {
            return false;
        }

        $logMessage = $message;
        if (is_null($message)) {
            $logMessage = $exception->getMessage();
        }

        $this->logInfo($logMessage);
        $this->logInfo("Process end with error for subscription profile # " . $subscription->getProfileId());

        $this->raiseFailureCount($subscription);
        $this->addHistory($subscription, $modifiedBy, $logMessage);
        $this->removeCurrentQuote();

        if (empty($message)) {
            $message = $this->getDefaultMessage();
        }

        return $this->sendEmail($subscription, $message);
    }

    public function raiseFailureCount($subscription)
    {
        $subscription->updateSubscriptionFailedCount();
    }

    public function addHistory($subscription, $modifiedBy = 0, $message = null)
    {
        $comment = __("There was an error when generating subscription order #%1", $subscription->getProfileId());
        if ($message) {
            $comment .= " Error: " . $message;
        }
        $subscription->addHistory($modifiedBy, $comment);
    }

    private function removeCurrentQuote()
    {
        $quote = $this->generateOrder->getCurrentQuote();
        if ($quote && $quote->getId()) {
            try {
                $this->generateOrder->setCurrentQuoteNull();
                $quote->delete();
            } catch (\Exception $ex) {
                $this->logInfo("quote is not delete " . $ex->getMessage());
            }
        }
        return true;
    }

    public function getDefaultMessage()
    {
        return __('Your subscription order failed. Due to card expiration, '
            . 'insufficient funds, card number change, non availability of product etc. '
            . 'In order to prevent discontinuation of subscription service, '
            . 'please verify your subscription information or contact store owner for more details.');
    }

    public function sendEmail($subscription, $message)
    {
        $generatedTime = $this->timezone->date()->format('r');
        $storeId = $subscription->getStoreId();

        $vars = [
            'placed_on' => $generatedTime,
            'subscription' => $subscription,
            'store_id' => $storeId,
            'failmessage' => $message,
        ];

        try {
            $emailTemplate = $this->getEmailTemplate();
            $this->send($vars, $emailTemplate, $subscription->getSubscriberEmail());
            $this->logInfo(sprintf("failed email sent successfully for # %s", $subscription->getProfileId()));
        } catch (\Exception $ex) {
            $this->logInfo(sprintf("failed email not send. Reason : %s", $ex->getMessage()));
        }

        return true;
    }

    public function getEmailTemplate()
    {
        return EmailService::EMAIL_PAYMENT_FAILED;
    }

    public function send($emailVariable, $type, $email)
    {
        if (!$this->helper->isPaymentFailedEmailSend($emailVariable['store_id'])) {
            return false;
        }

        $emailService = $this->emailService->create();
        $emailService->setStoreId($emailVariable['store_id']);
        $emailService->setTemplateVars($emailVariable);
        $emailService->setType($type);
        $emailService->setSendTo($email);
        $emailService->send();
    }
}
