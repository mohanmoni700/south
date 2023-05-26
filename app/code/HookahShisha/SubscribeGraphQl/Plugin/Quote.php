<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magedelight\Subscribenow\Plugin\Checkout\Model\Quote as Subject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Serialize\Serializer\Json;
use Magedelight\Subscribenow\Helper\Data as SubscribeNowHelper;

class Quote
{
    private CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry;
    private ScopeConfigInterface $scopeConfig;
    private Json $json;
    private SubscribeNowHelper $subscribeNowHelper;

    /**
     * @param CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param SubscribeNowHelper $subscribeNowHelper
     */
    public function __construct(
        CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry,
        ScopeConfigInterface          $scopeConfig,
        Json                          $json,
        SubscribeNowHelper            $subscribeNowHelper
    )
    {
        $this->cartItemSubscribeDataRegistry = $cartItemSubscribeDataRegistry;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->subscribeNowHelper = $subscribeNowHelper;
    }

    /**
     * @param Subject $subject
     * @param $parentSubject
     * @param object $product
     * @param null $request
     * @return array
     */
    public function beforeBeforeAddProduct(Subject $subject, $parentSubject, $product, $request = null): array
    {
        $subscriptionData = $this->cartItemSubscribeDataRegistry->getData()[0] ?? null;
        if ($subscriptionData && ($subscriptionData['is_subscription'] ?? null)) {
            $requestArray = $request->getData();
            $requestArray['options']['_1'] = 'subscription';
            $billingPeriod = $subscriptionData['billing_period'] ?? null;
            $this->validateBillingPeriod($billingPeriod);
            $requestArray['billing_period'] = $billingPeriod;
            $requestArray['subscription_start_date'] = $subscriptionData['subscription_start_date'] ?? null;
            $requestArray['subscription_end_date'] = $subscriptionData['subscription_end_date'] ?? null;
            $requestArray['subscription_end_cycle'] = $subscriptionData['subscription_end_cycle'] ?? null;
            $endType = $subscriptionData['end_type'] ?? null;
            $this->validateEndType($endType);
            $requestArray['end_type'] = $endType;
            $request->setData($requestArray);
        }
        return [$parentSubject, $product, $request];
    }

    protected function validateEndType($endType)
    {
        $allowedEndTypes = ['md_end_cycle', 'md_end_date', 'md_end_infinite'];
        if (!in_array($endType, $allowedEndTypes)) {
            throw new GraphQlInputException(__('Invalid End_Type'));
        }
    }

    protected function validateBillingPeriod($billingPeriod)
    {
        $allowedBillingPeriods = array_keys($this->subscribeNowHelper->getSubscriptionInterval());
        if (!in_array($billingPeriod, $allowedBillingPeriods)) {
            throw new GraphQlInputException(__('Invalid billing period'));
        }
    }
}

