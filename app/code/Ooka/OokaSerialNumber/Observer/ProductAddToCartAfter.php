<?php

declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Ooka\OokaSerialNumber\ViewModel\SerialCode;

class ProductAddToCartAfter implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var CartItemRepositoryInterface
     */
    private CartItemRepositoryInterface $cartItemRepository;
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @param SerialCode $serialCodeViewModel
     * @param ProductRepositoryInterface $productRepository
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        SerialCode $serialCodeViewModel,
        ProductRepositoryInterface $productRepository,
        CartItemRepositoryInterface $cartItemRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->serialCodeViewModel = $serialCodeViewModel;
        $this->productRepository = $productRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Execute method to set product attribute in quotelevel
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Log_Exception
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Quote $quote
         */
        $quote = $observer->getEvent()->getQuote();
        if (!empty($quote->getId()) && !empty($quote->getItemsCount())) {
            $updatedQuoteItems = [];
            $quoteItems = $quote->getAllItems();
            foreach ($quoteItems as $item) {
                $product = $this->productRepository->get($item->getSku(), false, $quote->getStoreId());
                $item->setData('is_serialize', $product->getData('ooka_require_serial_number'));
                $updatedQuoteItems[] = $item;
            }
            $quote->setItems($updatedQuoteItems);
        }
    }
}
