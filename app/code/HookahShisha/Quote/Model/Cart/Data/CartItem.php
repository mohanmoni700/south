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
     * @param string $sku
     * @param float $quantity
     * @param string|null $parentSku
     * @param array|null $selectedOptions
     * @param array|null $enteredOptions
     * @param string|null $alfaBundle
     * @param string|null $parentAlfaBundle
     */
    public function __construct(
        string $sku,
        float $quantity,
        string $parentSku = null,
        array $selectedOptions = null,
        array $enteredOptions = null,
        string $alfaBundle = null,
        string $parentAlfaBundle = null
    ) {
        parent::__construct($sku, $quantity, $parentSku, $selectedOptions, $enteredOptions);

        $this->alfaBundle = $alfaBundle;
        $this->parentAlfaBundle = $parentAlfaBundle;
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
}
