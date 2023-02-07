<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Quote
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Quote\Model\Cart\Data;

use Magento\Framework\Exception\InputException;
use HookahShisha\Quote\Model\Cart\Data\CartItem;
use Magento\Quote\Model\Cart\Data\CartItemFactory as SourceCartItemFactory;
use Magento\Quote\Model\Cart\Data\EnteredOption;
use Magento\Quote\Model\Cart\Data\SelectedOption;

/**
 * Creates CartItem DTO
 */
class CartItemFactory extends SourceCartItemFactory
{
    /**
     * Creates CartItem DTO
     *
     * @param array $data
     * @return CartItem
     * @throws InputException
     */
    public function create(array $data): CartItem
    {
        if (!isset($data['sku'], $data['quantity'])) {
            throw new InputException(__('Required fields are not present: sku, quantity'));
        }

        return new CartItem(
            $data['sku'],
            $data['quantity'],
            $data['parent_sku'] ?? null,
            isset($data['selected_options']) ? $this->createSelectedOptions($data['selected_options']) : [],
            isset($data['entered_options']) ? $this->createEnteredOptions($data['entered_options']) : [],
            $data['alfa_bundle'] ?? null,
            $data['parent_alfa_bundle'] ?? null,
            $data['super_pack_price'] ?? null
        );
    }

    /**
     * Creates array of Entered Options
     *
     * @param array $options
     * @return EnteredOption[]
     * @throws InputException
     */
    private function createEnteredOptions(array $options): array
    {
        return \array_map(
            function (array $option) {
                if (!isset($option['uid'], $option['value'])) {
                    throw new InputException(
                        __('Required fields are not present EnteredOption.uid, EnteredOption.value')
                    );
                }
                return new EnteredOption($option['uid'], $option['value']);
            },
            $options
        );
    }

    /**
     * Creates array of Selected Options
     *
     * @param string[] $options
     * @return SelectedOption[]
     */
    private function createSelectedOptions(array $options): array
    {
        return \array_map(
            function ($option) {
                return new SelectedOption($option);
            },
            $options
        );
    }
}
