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

namespace Magedelight\Subscribenow\Block\Cart\Item;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;

class Subscription extends \Magento\Checkout\Block\Cart\Additional\Info
{
    const TYPE_CONFIGURABLE = 'configurable';

    protected $registry;
    protected $productRepository;
    protected $subscriptionHelper;
    protected $subscriptionService;
    protected $timezone;
    protected $priceHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magedelight\Subscribenow\Helper\Data $subscriptionHelper,
        \Magedelight\Subscribenow\Model\Service\SubscriptionService $subscriptionService,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->subscriptionService = $subscriptionService;
        $this->timezone = $timezone;
        $this->priceHelper = $priceHelper;
    }

    public function setProduct()
    {
        $productId = $this->getItem()->getProduct()->getId();

        if(!$this->subscriptionHelper->isModuleEnable()) {
            return false;
        }

        $productType = $this->getItem()->getProductType();
        if ($productType == 'grouped') {
            $buyRequest = $this->getBuyRequest();
            $productId = $buyRequest['super_product_config']['product_id'];
        }

        $product = $this->productRepository->getById($productId, false, null, true);

        $this->registry->unregister('current_product');
        $this->registry->register('current_product', $product);

        $this->subscriptionService->getProductSubscriptionDetails($product, $this->getBuyRequest());
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getItemId()
    {
        return $this->getItem()->getId();
    }

    public function isBundle()
    {
        if ($this->getProduct()->getTypeId() == ProductType::TYPE_BUNDLE) {
            return true;
        }

        return false;
    }

    public function isConfigurable()
    {
        if ($this->getProduct()->getTypeId() == self::TYPE_CONFIGURABLE) {
            return true;
        }

        return false;
    }

    public function canDisplaySubscription()
    {
        return (bool) ($this->subscriptionHelper->isModuleEnable() && $this->isSubscriptionProduct());
    }

    public function isSubscriptionProduct()
    {
        return (bool) $this->getProduct()->getIsSubscription();
    }

    public function getFirstSubscriptionLabel()
    {
        return $this->subscriptionHelper->firstSubscriptionLabel();
    }

    public function getSecondSubscriptionLabel()
    {
        return $this->subscriptionHelper->secondSubscriptionLabel();
    }

    public function canPurchaseSeparately()
    {
        return (bool) ($this->getProduct()->getSubscriptionType() == PurchaseOption::EITHER);
    }

    public function isSubscriptionChecked()
    {
        $buyRequest = $this->getBuyRequest();
        $checked = $buyRequest['options']['_1'] ?? false;
        
        return (bool) ($checked == 'subscription');
    }

    public function getSubscription()
    {
        $product = $this->getProduct();
        $request = $this->getBuyRequest();
        $subscriptionData = $this->subscriptionService
            ->getProductSubscriptionDetails($product, $request)
            ->getSubscriptionData();
        return $subscriptionData;
    }

    public function canDisplayContent()
    {
        return $this->isSubscriptionChecked();
    }

    public function getBuyRequest()
    {
        return $this->getItem()->getBuyRequest()->getData();
    }

    public function getDiscountConfig()
    {
        $amount = ($this->getSubscription()->getDiscountType() == 'fixed')
                ? $this->getSubscription()->getBaseDiscountAmount()
                : (float) $this->getProduct()->getDiscountAmount();
        
        return $this->serialize->serialize([
            "product_type"      => $this->getProduct()->getTypeId(),
            "subscription"      => $this->getSubscription()->getIsSubscription(),
            "subscription_type" => $this->getSubscription()->getSubscriptionType(),
            "discount"          => $amount,
            "discount_type"     => $this->getSubscription()->getDiscountType(),
        ]);
    }
    
    public function getConfigDiscountAmount()
    {
        $amount = $this->getDiscountAmount();
        
        if ($this->getSubscription()->getDiscountType() != 'fixed'
            && $this->isConfigurable()) {
            $amount = 0;
        }
        
        return $amount;
    }
    
    public function getDiscountAmount($format = false)
    {
        $productPrice = $this->getProduct()->getData('final_price');
        $discount = $this->getSubscription()->getBaseDiscountAmount();
        
        if ($this->getSubscription()->getDiscountType() != 'fixed' && $format) {
            return (float) $this->getProduct()->getDiscountAmount() .'%';
        }

        if ($this->getSubscription()->getDiscountType() == 'fixed' && $format) {
            return $this->getCurrency($this->getProduct()->getDiscountAmount(), $format);
        }
        
        if ($this->isBundle()) {
            return $this->getCurrency($discount, $format);
        }
        
        if (0 > ($productPrice - $discount)) {
            $discount = $productPrice;
        }
        
        if ($this->getProduct()->getTypeId() == self::TYPE_CONFIGURABLE
            && $this->getSubscription()->getDiscountType() != 'fixed') {
            return $discount;
        }
        
        return $this->getCurrency($discount, $format);
    }

    public function getInitialAmount($format = false)
    {
        return $this->getCurrency($this->getSubscription()->getInitialAmount(), $format);
    }

    public function getTrialAmount($format = false)
    {
        if ($this->getSubscription()->getTrialAmount()) {
            return $this->getCurrency($this->getSubscription()->getTrialAmount(), $format);
        }
        return 0;
    }

    public function getCurrency($amount, $format = false)
    {
        return $this->priceHelper->currency($amount, $format);
    }
}
