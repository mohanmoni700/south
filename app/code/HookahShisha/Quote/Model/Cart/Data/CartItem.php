<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Quote
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Quote\Model\Cart\Data;

use Magento\Quote\Model\Cart\Data\CartItem as SourceCartItem;

/**
 * DTO represents Cart Item data
 */
class CartItem extends SourceCartItem
{
    /**
     * @var string
     */
    private $alfaBundle;

    /**
     * @var string
     */
    private $parentAlfaBundle;

    /**
     * @var string
     */
    private $superPackPrice;

    /**
     * @var bool
     */
    private $isSubscription;

    /**
     * @var string
     */
    private $billingPeriod;

    /**
     * @var string
     */
    private $subscriptionStartDate;

    /**
     * @var string
     */
    private $endType;

    /**
     * @var int
     */
    private $subscriptionEndCycle;

    /**
     * @var string
     */
    private $subscriptionEndDate;

    /**
     * @param string $sku
     * @param float $quantity
     * @param string|null $parentSku
     * @param array|null $selectedOptions
     * @param array|null $enteredOptions
     * @param string|null $alfaBundle
     * @param string|null $parentAlfaBundle
     * @param string|null $superPackPrice
     * @param bool|null $isSubscription
     * @param array|null $billingPeriod
     * @param string|null $subscriptionStartDate
     * @param string|null $endType
     * @param int|null $subscriptionEndCycle
     * @param string|null $subscriptionEndDate
     */
    public function __construct(
        string $sku,
        float $quantity,
        string $parentSku = null,
        array $selectedOptions = null,
        array $enteredOptions = null,
        string $alfaBundle = null,
        string $parentAlfaBundle = null,
        string $superPackPrice = null,
        bool $isSubscription = null,
        string $billingPeriod = null,
        string $subscriptionStartDate = null,
        string $endType = null,
        int $subscriptionEndCycle = null,
        string $subscriptionEndDate = null
    ) {
        parent::__construct($sku, $quantity, $parentSku, $selectedOptions, $enteredOptions);
        $this->alfaBundle = $alfaBundle;
        $this->parentAlfaBundle = $parentAlfaBundle;
        $this->superPackPrice = $superPackPrice;
        $this->isSubscription = $isSubscription;
        $this->billingPeriod = $billingPeriod;
        $this->subscriptionStartDate = $subscriptionStartDate;
        $this->endType = $endType;
        $this->subscriptionEndCycle = $subscriptionEndCycle;
        $this->subscriptionEndDate = $subscriptionEndDate;
    }

    /**
     * Returns cart item alfaBundle
     *
     * @return string
     */
    public function getAlfaBundle(): ?string
    {
        return $this->alfaBundle;
    }

    /**
     * Returns cart item parentAlfaBundle
     *
     * @return string
     */
    public function getParentAlfaBundle(): ?string
    {
        return $this->parentAlfaBundle;
    }

    /**
     * Returns cart item superPackPrice
     *
     * @return float
     */
    public function getSuperPackPrice(): ?float
    {
        return $this->superPackPrice;
    }

    /**
     * Returns cart item getSubscriptionStartDate
     *
     * @return string
     */
    public function getSubscriptionStartDate(): ?string
    {
        return $this->subscriptionStartDate;
    }

    /**
     * Returns cart item isSubscription
     *
     * @return bool
     */
    public function getIsSubscription(): ?bool
    {
        return $this->isSubscription;
    }

    /**
     * Returns cart item getSubbillingPeriod
     *
     * @return string
     */
    public function getSubbillingPeriod(): ?string
    {
        return $this->billingPeriod;
    }

    /**
     * Returns cart item getSubendType
     *
     * @return string
     */
    public function getSubendType(): ?string
    {
        return $this->endType;
    }

    /**
     * Returns cart item getSubscriptionEndCycle
     *
     * @return int
     */
    public function getSubscriptionEndCycle(): ?int
    {
        return $this->subscriptionEndCycle;
    }

    /**
     * Returns cart item getSubscriptionEndDate
     *
     * @return string
     */
    public function getSubscriptionEndDate(): ?string
    {
        return $this->subscriptionEndDate;
    }

    /**
     * Set superpack custom price for product
     *
     * @param float $price
     */
    public function setSuperPackPrice(float $price = 0)
    {
        $this->superPackPrice = $price;
    }
}
