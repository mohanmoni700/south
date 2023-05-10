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

namespace Magedelight\Subscribenow\Block\Customer\Account\View;

use Magedelight\Subscribenow\Block\Customer\Account\View;
use Magedelight\Subscribenow\Helper\Data as subscribeHelper;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\ProductFactory;

class Product extends View
{
    /**
     * @var subscribeHelper
     */
    private $subscriberHelper;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    protected $productFactory;

    protected $imageHelper;

    /**
     * @var Currency
     */
    private $orderCurrency = null;

    /**
     * @var Currency|null
     */
    private $baseCurrency = null;

    /**
     * Payment constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param subscribeHelper $subscribeHelper
     * @param TimezoneInterface $timezone
     * @param ProductRepositoryInterface $productRepository
     * @param CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        subscribeHelper $subscribeHelper,
        TimezoneInterface $timezone,
        ProductRepositoryInterface $productRepository,
        Json $serialize,
        CurrencyFactory $currencyFactory,
        ProductFactory $productFactory,
        Image $imageHelper,
        array $data = []
    ) {
    
        parent::__construct(
            $context,
            $registry,
            $subscribeHelper,
            $timezone,
            $productRepository,
            $serialize,
            $data
        );
        $this->currencyFactory = $currencyFactory;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->subscriberHelper = $subscribeHelper;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionStatus()
    {
        $status = $this->subscribeHelper->getStatusLabel();
        return $status[$this->getSubscription()->getSubscriptionStatus()];
    }

    /**
     * @return mixed
     */
    public function getQtyOrdered()
    {
        return (float) $this->getSubscription()->getQtySubscribed();
    }
    
    /**
     * Product Admin URL
     * @return string
     */
    public function getProductUrl()
    {
        if ($this->getSubscriptionProduct()) {
            return $this->getSubscriptionProduct()->getProductUrl();
        }
        return "#";
    }

    public function getProductImageUrl($id)
    {
        try 
        {
            $product = $this->productFactory->create()->load($id);
        } 
        catch (NoSuchEntityException $e) 
        {
            return 'Data not found';
        }
        $url = $this->imageHelper->init($product, 'product_base_image')->getUrl();
        return $url;
    }

    public function getProductOption()
    {
        $result = [];
        $productOptions = $this->getSubscription()->getAdditionalInfo('product_options');
        if ($productOptions) {
            foreach ($productOptions as $optionKey => $option) {
                if (in_array($optionKey, ['options', 'attributes_info'])) { // bundle_options
                    foreach ($option as $opt) {
                        $result[] = $opt;
                    }
                }
            }
        }

        return $result;
    }

      /**
     * @return bool
     */
    public function isCurrencyDifferent()
    {
        $subscription = $this->getSubscription();
        return $subscription->getCurrencyCode() != $subscription->getBaseCurrencyCode();
    }
    
    public function isDynamicPrice()
    {
        return (bool) $this->subscriberHelper->useDynamicPrice();
    }
    
    public function getCurrentCurrencyPrice($price)
    {
        if ($this->isCurrencyDifferent()) {
            $code = $this->getSubscription()->getCurrencyCode();
            $price = $this->getBaseCurrency()->convert($price, $code);
        }
        return $price;
    }

    public function bundleProductPricing($product)
    {
        if ($product->getTypeId() == 'bundle') {
            if ($product->getSubscriptionType() == PurchaseOption::SUBSCRIPTION) {
                $product->setSkipDiscount(true);
            }
        }
    }

    /**
     * @return string
     */
    public function getBaseBillingAmount()
    {
        if ($this->isDynamicPrice() && $this->getSubscriptionProduct()) {
            $product = $this->getSubscriptionProduct();
            if (!$this->getSubscription()->getIsTrial() && $this->getSubscription()->getTrialPeriodMaxCycles()) {
                $product->setSkipValidateTrial(true);
            }
            $this->bundleProductPricing($product);
            return $this->formatBasePrice($product->setSkipFutureSubscriptionValidation(true)->getFinalPrice());
        } else {
            return $this->formatBasePrice($this->getSubscription()->getBaseBillingAmount());
        }
    }
    
    /**
     * @return float|string
     */
    public function getBillingAmount()
    {
        if ($this->isDynamicPrice() && $this->getSubscriptionProduct()) {
            $product = $this->getSubscriptionProduct();
            if (!$this->getSubscription()->getIsTrial() && $this->getSubscription()->getTrialPeriodMaxCycles()) {
                $product->setSkipValidateTrial(true);
            }
            $this->bundleProductPricing($product);
            $price = $product->setSkipFutureSubscriptionValidation(true)->getFinalPrice();
            $billingamount = $this->getCurrentCurrencyPrice($price);
        } else {
            $billingamount = $this->getSubscription()->getBillingAmount();
        }
        
        $amount = $this->formatPrice($billingamount);
        if ($this->isCurrencyDifferent()) {
            $amount .= ' (' . $this->getBaseBillingAmount() . ')';
        }
        return $amount;
    }

    /**
     * @return float|string
     */
    public function getTrialAmount()
    {
        $amount =  $this->formatPrice($this->getSubscription()->getTrialBillingAmount());
        if ($this->isCurrencyDifferent()) {
            $amount .= ' (' . $this->formatBasePrice($this->getSubscription()->getBaseTrialBillingAmount()) . ')';
        }
        return $amount;
    }

    /**
     * @return float|string
     */
    public function getInitialAmount()
    {
        $amount = $this->formatPrice($this->getSubscription()->getInitialAmount());
        if ($this->isCurrencyDifferent()) {
            $amount .= ' (' . $this->formatBasePrice($this->getSubscription()->getBaseInitialAmount()) . ')';
        }
        return $amount;
    }

    /**
     * @param $amount
     * @return float|string
     */
    public function formatBasePrice($amount)
    {
        return $this->getBaseCurrency()->formatPrecision($amount, 2);
    }

    /**
     * @param $amount
     * @return float|string
     */
    public function formatPrice($amount)
    {
        return $this->getOrderCurrency()->formatPrecision($amount, 2);
    }

    /**
     * @return Currency|\Magento\Directory\Model\Currency
     */
    public function getBaseCurrency()
    {
        if ($this->baseCurrency == null) {
            $this->baseCurrency = $this->currencyFactory->create();
            $this->baseCurrency->load($this->getSubscription()->getBaseCurrencyCode());
        }
        return $this->baseCurrency;
    }

    /**
     * @return Currency|\Magento\Directory\Model\Currency
     */
    public function getOrderCurrency()
    {
        if ($this->orderCurrency == null) {
            $this->orderCurrency = $this->currencyFactory->create();
            $this->orderCurrency->load($this->getSubscription()->getCurrencyCode());
        }
        return $this->orderCurrency;
    }

}