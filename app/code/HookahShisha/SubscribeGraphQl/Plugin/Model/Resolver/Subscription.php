<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Resolver;

use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magedelight\Subscribenow\Model\Subscription as Subject;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class Subscription
{
    private JsonSerializer $jsonSerializer;

    /**
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(
        JsonSerializer $jsonSerializer
    )
    {

        $this->jsonSerializer = $jsonSerializer;
    }

    public function aroundIsSubscriptionProduct(
        Subject    $subject,
        callable $proceed,
        $product
    )
    {
        if ($product->getTypeId() !== "simple") {
           return $proceed($product);
        } else {
            if ($product->hasSkipDiscount() && $product->getSkipDiscount()) {
                return false;
            }

            if ($product->hasSkipValidateTrial() && $product->getSkipValidateTrial()) {
                return true;
            }

            $isSubscription = $product->getIsSubscription();
            $subscriptionType = $product->getSubscriptionType();
            if ($isSubscription && $subscriptionType == PurchaseOption::SUBSCRIPTION) {
                return true;
            } elseif ($this->isProductWithSubscriptionOption($subject, $product)) {
                return true;
            }

            return false;
        }

    }

    private function isProductWithSubscriptionOption(Subject $subject, $product)
    {
        $infoRequest = $product->getCustomOption('info_buyRequest');
        if ($infoRequest && $infoRequest->getValue()) {
            $requestData = $this->jsonSerializer->unserialize($infoRequest->getValue());
            if ($subject->getService()->checkProductRequest($requestData)) {
                return true;
            }
        }
        return false;
    }
}
