<?php

namespace HookahShisha\QuoteGraphQl\Model\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;
use Avalara\Excise\Helper\Config;

/**
 * Repository for quote item.
 */
class Repository extends \Magento\Quote\Model\Quote\Item\Repository
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CartItemInterfaceFactory
     */
    protected $itemDataFactory;

    /**
     * @var Config
     */
    protected $avalaraConfig;

    /**
     * @var CartItemProcessorInterface[]
     */
    protected $cartItemProcessors;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemInterfaceFactory $itemDataFactory
     * @param CartItemOptionsProcessor $cartItemOptionsProcessor
     * @param Config $avalaraConfig
     * @param CartItemProcessorInterface[] $cartItemProcessors
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        CartItemInterfaceFactory $itemDataFactory,
        CartItemOptionsProcessor $cartItemOptionsProcessor,
        Config $avalaraConfig,
        array $cartItemProcessors = []
    ) {
        parent::__construct(
            $quoteRepository,
            $productRepository,
            $itemDataFactory,
            $cartItemOptionsProcessor,
            $cartItemProcessors
        );

        $this->quoteRepository = $quoteRepository;
        $this->avalaraConfig = $avalaraConfig;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($cartId, $itemId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        if (!$quoteItem) {
            throw new NoSuchEntityException(
                __('The %1 Cart doesn\'t contain the %2 item.', $cartId, $itemId)
            );
        }

        try {
            $quote->removeItem($itemId);
            // restrict avalara tax request using flag for remove item
            $this->avalaraConfig->setAddressTaxable(false);
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__("The item couldn't be removed from the quote."));
        }

        return true;
    }
}
