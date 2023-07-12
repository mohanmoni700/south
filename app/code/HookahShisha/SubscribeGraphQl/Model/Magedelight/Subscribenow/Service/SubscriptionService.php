<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Magedelight\Subscribenow\Service;

use Magedelight\Subscribenow\Model\Service\SubscriptionService as MdSubscriptionService;
use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Source\BillingPeriodBy;
use Magedelight\Subscribenow\Model\Subscription;
use Magento\Catalog\Model\ProductFactory as ProductModelFactory;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterfaceFactory;
use Magedelight\Subscribenow\Model\Service\OrderServiceFactory;

/**
 * SubscriptionService
 */
class SubscriptionService extends MdSubscriptionService
{
    /**
     * @var SubscriptionHelper
     */
    private $subscriptionHelper;

    /**
     * @var DataObjectFactory
     */
    private $dataObject;

    /**
     * @var Json
     */
    private $serialize;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var CartRepositoryInterfaceFactory
     */
    protected $cartRepositoryFactory;

    /**
     * @var OrderServiceFactory
     */
    protected $orderServiceFactory;
    /**
     * @var ProductModelFactory
     */
    private $productModelFactory;

    /**
     * SubscriptionService constructor.
     *
     * @param SubscriptionHelper $subscriptionHelper
     * @param DataObjectFactory $dataObject
     * @param Json $serializer
     * @param TimezoneInterface $timezone
     * @param PriceCurrency $priceCurrency
     * @param CartRepositoryInterfaceFactory $cartRepositoryFactory
     * @param OrderServiceFactory $orderServiceFactory
     * @param ProductModelFactory $productModelFactory
     */
    public function __construct(
        SubscriptionHelper $subscriptionHelper,
        DataObjectFactory $dataObject,
        Json $serializer,
        TimezoneInterface $timezone,
        PriceCurrency $priceCurrency,
        CartRepositoryInterfaceFactory $cartRepositoryFactory,
        OrderServiceFactory $orderServiceFactory,
        ProductModelFactory $productModelFactory
    ) {
        $this->subscriptionHelper = $subscriptionHelper;
        $this->dataObject = $dataObject;
        $this->serialize = $serializer;
        $this->timezone = $timezone;
        $this->priceCurrency = $priceCurrency;
        $this->cartRepositoryFactory = $cartRepositoryFactory;
        $this->orderServiceFactory = $orderServiceFactory;
        $this->productModelFactory = $productModelFactory;

        parent::__construct(
            $subscriptionHelper,
            $dataObject,
            $serializer,
            $timezone,
            $priceCurrency,
            $cartRepositoryFactory,
            $orderServiceFactory,
            $productModelFactory
        );
    }

    /**
     * Calculate Max billing cycle from end date
     *
     * @param array $request
     * @return false|float|int|mixed|string|null
     * @throws LocalizedException
     * @since 200.7.0
     */
    public function endCycleCalculation($request)
    {
        $endType  = $request['end_type'];
        $endCycle = $request['subscription_end_cycle'];

        if ($endType == Subscription::END_TYPE_CYCLE) {
            return $endCycle;
        } elseif ($endType == Subscription::END_TYPE_DATE) {
            return $this->endDateCalculation($request);
        }
        return null;
    }

    /**
     * Calculate End Date
     *
     * @param array $request
     * @return float
     * @throws LocalizedException
     * @since 200.7.0
     */
    private function endDateCalculation($request)
    {
        if (isset($request['billing_period']) && $this->getBillingPeriodType() == BillingPeriodBy::CUSTOMER) {
            $subscriptionInterval = $this->getSubscriptionInterval($request['billing_period']);
        } else {
            $billingPeriod = $this->getBillingPeriod();
            $subscriptionInterval = $this->getSubscriptionInterval($billingPeriod);
        }

        $billingPeriod = $subscriptionInterval['interval_type'];
        $billingFrequency = $subscriptionInterval['no_of_interval'];
        $requestDate = (string)isset($request['subscription_start_date']) ? $request['subscription_start_date'] : '';
        $subscriptionStartDate = strtotime($requestDate);

        $endDate = (string)isset($request['subscription_end_date']) ? $request['subscription_end_date'] : '';
        $subscriptionEndDate = strtotime($endDate);

        $dateDiff = $subscriptionEndDate - $subscriptionStartDate;
        $dateDiff = round($dateDiff / (60 * 60 * 24));

        $year1 = date('Y', $subscriptionStartDate);
        $year2 = date('Y', $subscriptionEndDate);
        $month1 = date('m', $subscriptionStartDate);
        $month2 = date('m', $subscriptionEndDate);

        switch ($billingPeriod) {
            case 'day':
                $finalCycle = $dateDiff / $billingFrequency;
                break;
            case 'week':
                $finalCycle = ($dateDiff / $billingFrequency) / 7;
                break;
            case 'month':
                $finalCycle = (($year2 - $year1) * 12) + ($month2 - $month1);
                break;
            case 'year':
                $finalCycle = $year2 - $year1;
                break;
            default:
                $finalCycle = '';
        }

        if (is_float($finalCycle)) {
            $finalCycleArray = explode(".", (string)$finalCycle);
            if (isset($finalCycleArray[1]) && $finalCycleArray[1] > 0) {
                $finalCycle = $finalCycle + 1;
            }
        }

        $finalCycle = floor($finalCycle);

        if ($finalCycle < 1) {
            throw new LocalizedException(
                __('Subscription end date does not meet with selected date and frequency.')
            );
        }
        return $finalCycle;
    }
}
