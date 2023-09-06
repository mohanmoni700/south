<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Api;

use Alfakher\StockAlert\Api\Data\ProductAlertStockGuestUserInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface ProductAlertStockGuestUserRepositoryInterface
{
    /**
     * Save Product Alert Stock Guest User.
     *
     * @param ProductAlertStockGuestUserInterface $productAlert
     * @return ProductAlertStockGuestUserInterface
     */
    public function save(Data\ProductAlertStockGuestUserInterface $productAlert): ProductAlertStockGuestUserInterface;

    /**
     * Retrieve Product Alert Stock Guest User by ID.
     *
     * @param int $id
     * @return ProductAlertStockGuestUserInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): Data\ProductAlertStockGuestUserInterface;
}
