<?php

/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Catalog\Product\View;

use Magedelight\Subscribenow\Helper\Data as SubscriptionHelper;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;
use Magedelight\Subscribenow\Model\Source\PurchaseOption;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class Subscription extends Template
{
    const TYPE_CONFIGURABLE = 'configurable';
    const TYPE_GROUPED = 'grouped';

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var SubscriptionHelper
     */
    protected $subscriptionHelper;

    /**
     * @var SubscriptionService
     */
    protected $subscriptionService;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @var array
     */
    protected $info = [];

    /**
     * @var Json
     */
    protected $serialize;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Subscription constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param SubscriptionHelper $subscriptionHelper
     * @param SubscriptionService $subscriptionService
     * @param PriceHelper $priceHelper
     * @param Json $serialize
     * @param Session $customerSession
     * @param LocaleFormat $localeFormat
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscriptionHelper $subscriptionHelper,
        SubscriptionService $subscriptionService,
        priceHelper $priceHelper,
        Json $serialize,
        Session $customerSession,
        LocaleFormat $localeFormat,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->subscriptionService = $subscriptionService;
        $this->priceHelper = $priceHelper;
        $this->serialize = $serialize;
        $this->localeFormat = $localeFormat;
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }
    
    public function getGroupId()
    {
        return $this->customerSession->getCustomer()->getGroupId();
    }

    public function getProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

    public function canDisplaySubscription()
    {
        if ($this->subscriptionHelper->isModuleEnable()) {
            return $this->isSubscriptionProduct();
        }
        return false;
    }

    public function canDisplayContent()
    {
        return $this->getSubscription()->getSubscriptionType() == PurchaseOption::SUBSCRIPTION
            || $this->isSubscriptionChecked();
    }

    public function isSubscriptionProduct()
    {
        return $this->getProduct()->getIsSubscription();
    }

    public function getFirstSubscriptionLabel()
    {
        return $this->subscriptionHelper->firstSubscriptionLabel();
    }

    public function getSecondSubscriptionLabel()
    {
        return $this->subscriptionHelper->secondSubscriptionLabel();
    }

    public function isCartEdit()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();
        return in_array('checkout_cart_configure', $handles);
    }

    public function getItemBuyRequest()
    {
        $itemId = $this->getRequest()->getParam('id');
        $items = $this->subscriptionHelper->getCurrentQuote()->getAllItems();
        foreach ($items as $item) {
            if ($item->getId() == $itemId) {
                return $item->getOptionByCode('info_buyRequest')->getValue();
            }
        }
    }

    public function getRequestedParams()
    {
        $request = null;
        if ($this->isCartEdit()) {
            $buyRequest = $this->getItemBuyRequest();
            if ($buyRequest) {
                $request = $this->serialize->unserialize($buyRequest);
            }
        }
        return $request;
    }

    public function isSubscriptionChecked()
    {
        $request = $this->getSubscription()->getRequestPayload();
        return isset($request['is_subscription']) && $request['is_subscription'] == 1;
    }

    public function getSubscription()
    {
        $product = $this->getProduct();
        $request = $this->getRequestedParams();
        $subscriptionData = $this->subscriptionService
            ->getProductSubscriptionDetails($product, $request)
            ->getSubscriptionData();
        return $subscriptionData;
    }

    public function canPurchaseSeparately()
    {
        return $this->getSubscription()->getSubscriptionType() == PurchaseOption::EITHER;
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

    public function isGrouped()
    {
        if ($this->getProduct()->getTypeId() == self::TYPE_GROUPED) {
            return true;
        }
        return false;
    }

    public function getDiscountConfig()
    {
        $amount = ($this->getSubscription()->getDiscountType() == 'fixed')
                ? $this->getSubscription()->getBaseDiscountAmount()
                : (float) $this->getProduct()->getDiscountAmount();

        $data = [
            "product_type" => $this->getProduct()->getTypeId(),
            "subscription" => $this->getSubscription()->getIsSubscription(),
            "subscription_type" => $this->getSubscription()->getSubscriptionType(),
            "discount" => $amount,
            "discount_type" => $this->getSubscription()->getDiscountType(),
        ];

        if ($this->isGrouped()) {
            $data['discount_locale'] = $this->getCurrency($amount);
            $data['currency'] = $this->localeFormat->getPriceFormat();
        }

        return $this->serialize->serialize($data);
    }

    public function getConfigDiscountAmount()
    {
        $amount = $this->getDiscountAmount();

        if ($this->isBundle() ||
            ($this->isConfigurable() && $this->getSubscription()->getDiscountType() != 'fixed')
        ) {
            $amount = 0;
        }

        return $amount;
    }

    public function getDiscountAmount($format = false)
    {
        $productPrice = $this->getProduct()->getFinalPrice();
        $discount = $this->getSubscription()->getBaseDiscountAmount();

        if ($this->getSubscription()->getDiscountType() != 'fixed' && $format) {
            return (float) $this->getProduct()->getDiscountAmount() . '%';
        }

        if ($this->isBundle() || $this->isGrouped()) {
            return $this->getCurrency($discount, $format);
        }

        if (0 > ($productPrice - $discount) && !$format) {
            $discount = $productPrice;
        }

        if ($this->getProduct()->getTypeId() == self::TYPE_CONFIGURABLE && $this->getSubscription()->getDiscountType() != 'fixed') {
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

    public function getSubscriptionLabel()
    {
        $subscriptionWithDiscount = __("Subscribe this product");

        if ((int)$this->getDiscountAmount()) {
            $subscriptionWithDiscount = __("Subscribe with discount - %1", $this->getDiscountAmount(true));
        }

        if ($this->isBundle()) {
            if ($this->getSubscription()->getDiscountType() == 'fixed') {
                $subscriptionWithDiscount = __(
                    "Subscribe with discount - %1 <br> &emsp; (on every child products)",
                    $this->getDiscountAmount(true)
                );
            } else {
                $subscriptionWithDiscount = __(
                    "Subscribe with discount - %1 <br> &emsp; (Percentage calculate on every child products)",
                    $this->getDiscountAmount(true)
                );
            }
        }

        return $subscriptionWithDiscount;
    }

    public function getJsonConfig()
    {
        $config = [];
        $config['_1']['subscription'] = [
            'prices' => [
                'oldPrice' => [
                    'amount' => 0,
                    'adjustments' => [],
                ],
                'basePrice' => [
                    'amount' => '-' . $this->getConfigDiscountAmount(),
                ],
                'finalPrice' => [
                    'amount' => '-' . $this->getConfigDiscountAmount(),
                ],
            ],
            'type' => 'fixed',
            'name' => 'Subscribe This Product',
        ];

        return $this->serialize->serialize($config);
    }

    public function getCurrency($amount, $format = false)
    {
        return $this->priceHelper->currency($amount, $format);
    }

    public function isAllowedCustomerGroup()
    {
        $subscriptionType = $this->getProduct()->getSubscriptionType();
        if($this->isSubscriptionProduct()) {
            if ($subscriptionType == 'subscription' || $subscriptionType == 'either') {
                $currentCustomerGroupId = $this->customerSession->getCustomerGroupId();
                $allowedCustomerGroupIds = $this->subscriptionHelper->getAllowedCustomerGroups();
                if($this->subscriptionHelper->isAllowToAddtoCart()){
                    $guestid = 0;
                    array_push($allowedCustomerGroupIds,$guestid);
                }
                if (!in_array($currentCustomerGroupId, $allowedCustomerGroupIds)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function isSubscriptionAllowedCustomerGroup()
    {
        $subscriptionType = $this->getProduct()->getSubscriptionType();
        if ($subscriptionType == 'either') {
            $currentCustomerGroupId = $this->customerSession->getCustomerGroupId();
            $allowedCustomerGroupIds = $this->subscriptionHelper->getAllowedCustomerGroups();
            if($this->subscriptionHelper->isAllowToAddtoCart()){
                $guestid = 0;
                array_push($allowedCustomerGroupIds,$guestid);
            }
            if (!in_array($currentCustomerGroupId, $allowedCustomerGroupIds)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return mixed
     * @since 200.7.0
     */
    public function isAllowedSubscriptionEndDate()
    {
        return $this->getProduct()->getAllowSubscriptionEndDate();
    }
}
