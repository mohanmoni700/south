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

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\ProfileInfo;

use Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\ProfileInfo as ParentBlock;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magedelight\Subscribenow\Model\Service\PaymentService;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Serialize\Serializer\Json;

class Payment extends ParentBlock
{

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var Currency
     */
    private $orderCurrency = null;

    /**
     * @var Currency|null
     */
    private $baseCurrency = null;

    /**
     * Payment Constructor
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helper
     * @param TimezoneInterface $timezone
     * @param PaymentHelper $paymentHelper
     * @param PriceHelper $priceHelper
     * @param PaymentService $paymentService
     * @param CurrencyFactory $currencyFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        Data $helper,
        TimezoneInterface $timezone,
        Json $serialize,
        PaymentHelper $paymentHelper,
        PriceHelper $priceHelper,
        PaymentService $paymentService,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->paymentService = $paymentService;
        $this->paymentHelper = $paymentHelper;
        $this->priceHelper = $priceHelper;
        $this->currencyFactory = $currencyFactory;
        
        parent::__construct($context, $registry, $productRepository, $helper, $timezone, $serialize, $data);
    }
    
    /**
     * Check order currency is different
     * from base currency
     */
    public function isCurrencyDifferent()
    {
        $subscription = $this->getSubscription();
        return $subscription->getCurrencyCode() != $subscription->getBaseCurrencyCode();
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
    
    public function isDynamicPrice()
    {
        return (bool) $this->helper->useDynamicPrice();
    }
    
    public function getCurrentCurrencyPrice($price)
    {
        if ($this->isCurrencyDifferent()) {
            $code = $this->getSubscription()->getCurrencyCode();
            $price = $this->getBaseCurrency()->convert($price, $code);
        }
        return $price;
    }
    
    /**
     * @return string
     */
    public function getBaseTaxAmount()
    {
        return $this->formatBasePrice($this->getSubscription()->getBaseTaxAmount());
    }
    
    /**
     * @return string
     */
    public function getBaseInitialAmount()
    {
        return $this->formatBasePrice($this->getSubscription()->getBaseInitialAmount());
    }
    
    /**
     * @return string
     */
    public function getBaseTrialBillingAmount()
    {
        return $this->formatBasePrice($this->getSubscription()->getBaseTrialBillingAmount());
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
     * Get Tax Amount
     */
    public function getTaxAmount()
    {
        $amount = $this->formatPrice($this->getSubscription()->getTaxAmount());
        if ($this->isCurrencyDifferent()) {
            $amount .= ' (' . $this->getBaseTaxAmount() . ')';
        }
        return $amount;
    }
    
    /**
     * Get Initial Amount Title
     */
    public function getInitialAmountTitle()
    {
        return $this->helper->getInitAmountTitle();
    }

    /**
     * Get Initial Amount
     */
    public function getInitialAmount()
    {
        $amount = $this->formatPrice($this->getSubscription()->getInitialAmount());
        if ($this->isCurrencyDifferent()) {
            $amount .= ' (' . $this->getBaseInitialAmount() . ')';
        }
        return $amount;
    }
    
    /**
     * Get Trial Amount Title
     */
    public function getTrialAmountTitle()
    {
        return $this->helper->getTrialAmountTitle();
    }

    /**
     * Get Trial Amount
     */
    public function getTrialAmount()
    {
        $amount = $this->formatPrice($this->getSubscription()->getTrialBillingAmount());
        if ($this->isCurrencyDifferent()) {
            $amount .= ' (' . $this->getBaseTrialBillingAmount() . ')';
        }
        return $amount;
    }

    /**
     * Check Card Info row is
     * Show or not based on payment method
     * @return bool
     */
    public function canDisplayCardInfo()
    {
        $code = $this->getSubscription()->getPaymentMethodCode();
        $methods = $this->helper->getNACardInfoMethods();
        if (in_array($code, $methods)) {
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getCardInfo()
    {
        $code = $this->getSubscription()->getPaymentMethodCode();
        $token = $this->getSubscription()->getPaymentToken();
        $customerId = $this->getSubscription()->getCustomerId();
        $paymentService = $this->paymentService->getByPaymentCode($code, $token, $customerId);
        
        if ($paymentService) {
            return $paymentService->getCardInfo();
        }
        
        return null;
    }

    public function getSaveCards()
    {
        $code = $this->getSubscription()->getPaymentMethodCode();
        $token = $this->getSubscription()->getPaymentToken();
        $customerId = $this->getSubscription()->getCustomerId();
        return $this->paymentService->getByPaymentCode($code, $token, $customerId)->getSavedCards();
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        $code = $this->getSubscription()->getPaymentMethodCode();
        $list = $this->paymentHelper->getPaymentMethodList(false);
        $list['magedelight_ewallet'] = 'Magedelight EWallet';
        return $list[$code];
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
