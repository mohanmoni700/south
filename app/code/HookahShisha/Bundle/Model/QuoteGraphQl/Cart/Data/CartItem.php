<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace HookahShisha\Bundle\Model\QuoteGraphQl\Cart\Data;

/**
 * DTO represents Cart Item data
 */
class CartItem extends \Magento\Quote\Model\Cart\Data\CartItem
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $parentSku;

    /**
     * @var string
     */
    private $alfaIsBundle;

    /**
     * @var SelectedOption[]
     */
    private $selectedOptions;

    /**
     * @var EnteredOption[]
     */
    private $enteredOptions;

    /**
     * @param string $sku
     * @param float $quantity
     * @param string|null $alfaIsBundle
     * @param string|null $parentSku
     * @param array|null $selectedOptions
     * @param array|null $enteredOptions
     */
    public function __construct(
        string $sku,
        float $quantity,
        string $alfaIsBundle = null,
        string $parentSku = null,
        array $selectedOptions = null,
        array $enteredOptions = null
    ) {
        $this->sku = $sku;
        $this->quantity = $quantity;
        $this->alfaIsBundle = $alfaIsBundle;
        $this->parentSku = $parentSku;
        $this->selectedOptions = $selectedOptions;
        $this->enteredOptions = $enteredOptions;
    }

    /**
     * Returns cart item SKU
     *
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * Returns cart item quantity
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * Returns parent SKU
     *
     * @return string|null
     */
    public function getAlfaIsBundle():  ? string
    {
        return $this->alfaIsBundle;
    }

    /**
     * Returns parent SKU
     *
     * @return string|null
     */
    public function getParentSku() :  ? string
    {
        return $this->parentSku;
    }

    /**
     * Returns selected options
     *
     * @return SelectedOption[]|null
     */
    public function getSelectedOptions() :  ? array
    {
        return $this->selectedOptions;
    }

    /**
     * Returns entered options
     *
     * @return EnteredOption[]|null
     */
    public function getEnteredOptions() :  ? array
    {
        return $this->enteredOptions;
    }
}
