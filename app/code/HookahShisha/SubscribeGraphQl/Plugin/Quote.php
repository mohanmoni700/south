<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin;

use HookahShisha\SubscribeGraphQl\Model\CartItemSubscribeDataRegistry;
use Magedelight\Subscribenow\Plugin\Checkout\Model\Quote as Subject;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magedelight\Subscribenow\Helper\Data as SubscribeNowHelper;

/**
 * Quote
 */
class Quote
{
    /**
     * @var CartItemSubscribeDataRegistry
     */
    private CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry;

    /**
     * @var SubscribeNowHelper
     */
    private SubscribeNowHelper $subscribeNowHelper;

    /**
     * @param CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry
     * @param SubscribeNowHelper $subscribeNowHelper
     */
    public function __construct(
        CartItemSubscribeDataRegistry $cartItemSubscribeDataRegistry,
        SubscribeNowHelper            $subscribeNowHelper
    )
    {
        $this->cartItemSubscribeDataRegistry = $cartItemSubscribeDataRegistry;
        $this->subscribeNowHelper = $subscribeNowHelper;
    }

    /**
     * @param Subject $subject
     * @param $parentSubject
     * @param object $product
     * @param null $request
     * @return array
     * @throws GraphQlInputException
     */
    public function beforeBeforeAddProduct(Subject $subject, $parentSubject, $product, $request = null): array
    {
        $subscriptionData = $this->cartItemSubscribeDataRegistry->getData()[$product->getSku()] ?? null;
        if ($request instanceof \Magento\Framework\DataObject) {
            $requestArray = $request->getData();
        } else {
            $requestArray = [];
        }
        $isExistingProduct = ($requestArray['options']['_1'] ?? null) == 'subscription';
        if (!$isExistingProduct && $subscriptionData && ($subscriptionData['is_subscription'] ?? null)) {
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

    /**
     * @throws GraphQlInputException
     */
    protected function validateEndType($endType): void
    {
        $allowedEndTypes = ['md_end_cycle', 'md_end_date', 'md_end_infinite'];
        if (!in_array($endType, $allowedEndTypes)) {
            throw new GraphQlInputException(__('Invalid End_Type'));
        }
    }

    /**
     * @throws GraphQlInputException
     */
    protected function validateBillingPeriod($billingPeriod): void
    {
        $allowedBillingPeriods = array_keys($this->subscribeNowHelper->getSubscriptionInterval());
        if (!in_array($billingPeriod, $allowedBillingPeriods)) {
            throw new GraphQlInputException(__('Invalid billing period'));
        }
    }
}

