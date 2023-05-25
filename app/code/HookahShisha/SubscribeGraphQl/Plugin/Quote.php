<?php

namespace HookahShisha\SubscribeGraphQl\Plugin;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magedelight\Subscribenow\Plugin\Checkout\Model\Quote as Subject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Quote
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
     * @param Subject $subject
     * @param $parentSubject
     * @param object $product
     * @param null $request
     * @return array
     */
    public function beforeBeforeAddProduct(Subject $subject, $parentSubject, $product, $request = null): array
    {
        $subscriptionData = $this->cartItemSubscribeDataRegistry->getData()[0] ?? null;
        if($subscriptionData && ($subscriptionData['is_subscription'] ?? null)) {
            $requestArray = $request->getData();
            $requestArray['options']['_1'] = 'subscription';
            $requestArray['billing_period'] = $subscriptionData['billing_period'];
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
