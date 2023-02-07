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
     * @param string $sku
     * @param float $quantity
     * @param string|null $parentSku
     * @param array|null $selectedOptions
     * @param array|null $enteredOptions
     * @param string|null $alfaBundle
     * @param string|null $parentAlfaBundle
     * @param string|null $superPackPrice
     */
    public function __construct(
        string $sku,
        float $quantity,
        string $parentSku = null,
        array $selectedOptions = null,
        array $enteredOptions = null,
        string $alfaBundle = null,
        string $parentAlfaBundle = null,
        string $superPackPrice = null
    ) {
        parent::__construct($sku, $quantity, $parentSku, $selectedOptions, $enteredOptions);

        $this->alfaBundle = $alfaBundle;
        $this->parentAlfaBundle = $parentAlfaBundle;
        $this->superPackPrice = $superPackPrice;
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
     * Set superpack custom price for product
     *
     * @param float $price
     */
    public function setSuperPackPrice(float $price = 0)
    {
        $this->superPackPrice = $price;
    }
}
