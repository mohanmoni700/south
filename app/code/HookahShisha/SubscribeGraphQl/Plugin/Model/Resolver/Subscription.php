<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Resolver;

use Magedelight\Subscribenow\Model\Service\DiscountService;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magedelight\Subscribenow\Model\Subscription as Subject;
use Magento\Bundle\Model\Product\TypeFactory as BundleTypeFactory;
use Magento\Bundle\Model\Selection;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Session\SessionManagerInterface;

class Subscription
{
    private JsonSerializer $jsonSerializer;
    private DiscountService $discountService;
    private SubscriptionService $service;
    private $hasParent = false;
    private $parentProduct = null;
    private $skipProductTrialValidation = false;
    private ProductFactory $productFactory;
    private Http $request;
    private BundleTypeFactory $bundleTypeFactory;
    const BUNDLE = 'BUNDLE_PRODUCT';
    const CACHE_OPTION = 'CACHE_PRODUCT';
    private SessionManagerInterface $sessionManager;
    private Selection $selection;

    /**
     * @param JsonSerializer $jsonSerializer
     */
    public function __construct(
        JsonSerializer          $jsonSerializer,
        SubscriptionService     $service,
        Http                    $request,
        BundleTypeFactory       $bundleTypeFactory,
        ProductFactory          $productFactory,
        SessionManagerInterface $sessionManager,
        Selection               $selection,
        DiscountService         $discountService
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->discountService = $discountService;
        $this->service = $service;
        $this->productFactory = $productFactory;
        $this->request = $request;
        $this->bundleTypeFactory = $bundleTypeFactory;
        $this->sessionManager = $sessionManager;
        $this->selection = $selection;
    }

    public function aroundIsSubscriptionProduct(
        Subject  $subject,
        callable $proceed,
        $product
    ) {
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

    /**
     * @param Subject $subject
     * @param $product
     * @return bool
     */
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


    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param $finalPrice
     * @param $product
     * @param $convert
     * @return mixed
     */
    public function aroundGetSubscriptionDiscount(
        Subject  $subject,
        callable $proceed,
        $finalPrice,
        $product,
        $convert = false
    ) {
        if (!$subject->helper->isModuleEnable()) {
            return $finalPrice;
        }

        $this->hasParent = false;
        $this->parentProduct = null;

        if ($product->getTypeId() == 'bundle' &&
            $product->hasSkipValidateTrial() && $product->getSkipValidateTrial()) {
            $this->skipProductTrialValidation = true;
        }

        $optionPrice = $this->getOptionPrice($product);
        $price = $finalPrice;

        $this->parentProduct = $this->getBundleProduct($product);
        if (!$this->parentProduct) {
            $this->parentProduct = $this->getParentProduct($product);
        }

        if ($product->getPrice() != 0 && $subject->isSubscriptionProduct($product) || $this->parentProduct) {
            $price = $finalPrice - $optionPrice;
            $type = $this->getDiscountType($product);

            $discount = ($convert) ? $this->service->getConvertedPrice($this->getDiscountAmount($product)) :
                $this->getDiscountAmount($product);

            $price = $this->discountService->calculateDiscountByValue($price, $type, $discount);
            $price += $optionPrice;

            if ($product->hasSkipValidateTrial() && $product->getSkipValidateTrial()) {
                return max(0, $price);
            }

            if ($subject->getService()->isFutureSubscription($product) ||
                ($product->getAllowTrial() && $product->getTrialAmount() > 0)) {
                $product->setCustomPrice(0);
            }

            /** set custom price to product for trial & future items
             * && show custom price everywhere excluding product detail page */
            if ($product->hasCustomPrice() && $subject->request->getRouteName() != 'catalog') {
                $price = $product->getCustomPrice(0);
            }
        }

        if ($product->hasSkipDiscount() && $product->getSkipDiscount()) {
            return $price - $optionPrice;
        }

        return max(0, $price);
    }

    /**
     * @param $product
     * @return int|mixed
     */
    private function getOptionPrice($product)
    {
        $finalPrice = 0;
        $optionIds = $product->getCustomOption('option_ids');
        if ($optionIds) {
            $basePrice = $finalPrice;
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {
                    $confItemOption = $product->getCustomOption('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setConfigurationItemOption($confItemOption);
                    $finalPrice += $group->getOptionPrice($confItemOption->getValue(), $basePrice);
                }
            }
        }

        return $finalPrice;
    }

    /**
     * @param $product
     * @return mixed
     */
    private function getDiscountType($product)
    {
        if ($this->parentProduct) {
            return $this->parentProduct->getDiscountType();
        }
        return $product->getDiscountType();
    }

    /**
     * @param $product
     * @return mixed
     */
    private function getDiscountAmount($product)
    {
        if ($this->parentProduct) {
            return $this->parentProduct->getDiscountAmount();
        }
        return $product->getDiscountAmount();
    }

    /**
     *To get the bundle product
     *
     * @param $product
     * @return bool|Product
     */
    private function getBundleProduct($product)
    {
        $bundleId = $product->getData('parent_product_id');
        $bundleKey = self::BUNDLE . $product->getId();
        $sessionBundleId = $this->sessionManager->getData($bundleKey);

        //Get the parent product from the session
        if (!isset($sessionBundleId) && isset($bundleId)) {
            $this->sessionManager->setData(
                $bundleKey,
                $bundleId
            );
        } elseif (isset($sessionBundleId)) {
            $bundleId = $sessionBundleId;
            foreach ($this->sessionManager->getData() as $key => $value) {
                if (strpos($key, self::BUNDLE) === 0) {
                    $this->sessionManager->unsetData($key);
                }
            }
        }

        return isset($bundleId) ? $this->productFactory->create()->load($bundleId) : false;
    }

    /**
     *To get the bundle in case of cart
     *
     * @param $product
     * @return false|Product
     */
    private function getParentProduct($product)
    {
        $optionIds = $product->getData('_cache_instance_used_selections_ids');
        if ($product->getTypeId() == 'bundle' && isset($optionIds)) {
            foreach ($optionIds as $optionId) {
                $productOption = $this->selection->load($optionId);
                $cacheKey = self::CACHE_OPTION . $productOption->getProductId();
                $cacheData = $this->sessionManager->getData($cacheKey);
                if (!isset($cacheData)) {
                    $this->sessionManager->setData(
                        $cacheKey,
                        $product->getId()
                    );
                }
            }
        } elseif ($product->getTypeId() == 'simple') {
            $cacheKey = self::CACHE_OPTION . $product->getId();
            $bundleId = $this->sessionManager->getData($cacheKey);
            foreach ($this->sessionManager->getData() as $key => $value) {
                if (strpos($key, self::CACHE_OPTION) === 0) {
                    $this->sessionManager->unsetData($key);
                }
            }
        }
        return isset($bundleId) ? $this->productFactory->create()->load($bundleId) : false;
    }
}
