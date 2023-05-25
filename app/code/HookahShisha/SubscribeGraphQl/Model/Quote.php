<?php

namespace HookahShisha\SubscribeGraphQl\Model;

use Magedelight\Subscribenow\Plugin\Checkout\Model\Quote as Subject;

class Quote
{

    private CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry;

    /**
     * @param CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
     */
    public function __construct(
        CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
    )
    {
        $this->cartItemSubscribeDataRegistry = $cartItemSubscribeDataRegistry;
    }

    public function beforeBeforeAddProduct(Subject $subject, $parentsubject, $product, $request = null)
    {
        $subscriptionData = $this->cartItemSubscribeDataRegistry->getData()[0] ?? null;
        print_r($subscriptionData);
        die();
        if($subscriptionData) {
            $requestArray = $request->getData();
            $requestArray['options']['_1'] = 'subscription';
            $requestArray['billing_period'] = '_1669985355611_611';
            $requestArray['subscription_start_date'] = '24-05-2023';
            $requestArray['subscription_end_cycle'] = null;
            $requestArray['end_type'] = 'md_end_infinite';
            $request->setData($requestArray);
        }
        return [$parentsubject, $product, $request];
    }
}
