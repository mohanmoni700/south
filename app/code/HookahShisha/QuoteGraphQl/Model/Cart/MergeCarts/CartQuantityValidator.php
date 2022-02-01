<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_QuoteGraphQl
 * @author    Janis Verins <info@corra.com>
 */

declare(strict_types=1);

namespace HookahShisha\QuoteGraphQl\Model\Cart\MergeCarts;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\QuoteGraphQl\Model\Cart\MergeCarts\CartQuantityValidator as SourceCartQuantityValidator;

class CartQuantityValidator extends SourceCartQuantityValidator
{
    /**
     * @var CartItemRepositoryInterface
     */
    private CartItemRepositoryInterface $cartItemRepository;

    /**
     * @var StockRegistryInterface
     */
    private StockRegistryInterface $stockRegistry;

    /**
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        CartItemRepositoryInterface $cartItemRepository,
        StockRegistryInterface $stockRegistry
    ) {
        parent::__construct($cartItemRepository, $stockRegistry);

        $this->cartItemRepository = $cartItemRepository;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Validate combined cart quantities to make sure they are within available stock
     *
     * @param CartInterface $customerCart
     * @param CartInterface $guestCart
     * @return bool
     */
    public function validateFinalCartQuantities(CartInterface $customerCart, CartInterface $guestCart): bool // NOSONAR
    {
        $modified = false;
        /** @var CartItemInterface $guestCartItem */
        foreach ($guestCart->getAllVisibleItems() as $guestCartItem) {
            foreach ($customerCart->getAllItems() as $customerCartItem) {
                if ($customerCartItem->compare($guestCartItem)) {
                    $product = $customerCartItem->getProduct();
                    $stockCurrentQty = $this->stockRegistry->getStockStatus(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    )->getQty();
                    if ($stockCurrentQty < $guestCartItem->getQty() + $customerCartItem->getQty()
                        // Need this check because configurable item will always have qty 0 and if its removed, then
                        // simple product which has this parent is also removed, which is what we don't want.
                        && !$guestCartItem->getHasChildren()
                    ) {
                        try {
                            $guestCartItemAlfaBundle = $guestCartItem->getAlfaBundle();

                            $this->deleteAssociatedAlfaBundleItems($guestCart, $guestCartItemAlfaBundle);
                            $this->cartItemRepository->deleteById($guestCart->getId(), $guestCartItem->getItemId());

                            $modified = true;
                        } catch (NoSuchEntityException|CouldNotSaveException $e) {
                            continue;
                        }
                    }
                }
            }
        }
        return $modified;
    }

    /**
     * Delete associated alfa bundle items if main item has no qty left in stock
     *
     * @param CartInterface $guestCart
     * @param string $guestCartItemAlfaBundle
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function deleteAssociatedAlfaBundleItems(CartInterface $guestCart, string $guestCartItemAlfaBundle)
    {
        foreach ($guestCart->getAllVisibleItems() as $item) {
            if ($item->getParentAlfaBundle() == $guestCartItemAlfaBundle) {
                $this->cartItemRepository->deleteById($guestCart->getId(), $item->getItemId());
            }
        }
    }
}
