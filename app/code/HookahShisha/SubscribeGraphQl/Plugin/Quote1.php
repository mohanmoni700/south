<?php

namespace HookahShisha\SubscribeGraphQl\Plugin;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magedelight\Subscribenow\Plugin\Checkout\Model\Quote;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Quote1
{
    private CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry;
    private ScopeConfigInterface $scopeConfig;
    private Json $json;

    /**
     * @param CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     */
    public function __construct(
        CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry,
        ScopeConfigInterface          $scopeConfig,
        Json                          $json
    )
    {
        $this->cartItemSubscribeDataRegistry = $cartItemSubscribeDataRegistry;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
    }

    /**
     * @param Quote $subject
     * @param object $subject
     * @param object $product
     * @param null $request
     * @return array
     */
    public function beforeBeforeAddProduct(Quote $subject, $parentSubject, $product, $request = null): array
    {
        $subscriptionData = $this->cartItemSubscribeDataRegistry->getData()[0] ?? null;
        if($subscriptionData && ($subscriptionData['is_subscription'] ?? null)) {
            $requestArray = $request->getData();
            $billingPeriod = $subscriptionData['billing_period'];
            $subscriptionIntervals = $this->scopeConfig->getValue('md_subscribenow/general/manage_subscription_interval');
            $subscriptionIntervals = $this->json->unserialize($subscriptionIntervals);
            $billingPeriodTimeStamp = null;
            foreach ($subscriptionIntervals as $key => $subscriptionInterval) {
                if(($subscriptionInterval['interval_type'].'_'.$subscriptionInterval['no_of_interval']) == $billingPeriod) {
                    $billingPeriodTimeStamp = $key;
                }
            }
            $requestArray['options']['_1'] = 'subscription';
            $requestArray['billing_period'] = $billingPeriodTimeStamp;
            $requestArray['subscription_start_date'] = $subscriptionData['subscription_start_date'];
            $requestArray['subscription_end_date'] = $subscriptionData['subscription_end_date'];
            $requestArray['subscription_end_cycle'] = $subscriptionData['subscription_end_cycle'];
            $requestArray['end_type'] = $subscriptionData['end_type'];
            $request->setData($requestArray);
        }
        // TODO: Implement plugin method.
        return [$parentSubject, $product, $request];
    }
}
