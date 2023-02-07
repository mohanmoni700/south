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

namespace Magedelight\Subscribenow\Observer;

use Magedelight\Subscribenow\Model\Service\SubscriptionFailedService;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SubscriptionFailedObserver implements ObserverInterface
{
    private $failedService;

    public function __construct(
        SubscriptionFailedService $failedService
    ) {
        $this->failedService = $failedService;
    }

    public function execute(EventObserver $observer)
    {
        $subscription = $observer->getData('subscription');
        $exception = $observer->getData('exception');
        $errorMessage = $observer->getData('error_message');
        $modifiedBy = $observer->getData('modified_by');

        $this->failedService->update($subscription, $exception, $errorMessage, $modifiedBy);

        return $this;
    }
}
