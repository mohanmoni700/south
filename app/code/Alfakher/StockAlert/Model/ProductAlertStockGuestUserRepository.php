<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model;

use Alfakher\StockAlert\Api\Data;
use Alfakher\StockAlert\Api\Data\ProductAlertStockGuestUserInterface;
use Alfakher\StockAlert\Api\ProductAlertStockGuestUserRepositoryInterface;
use Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser as ProductAlertStockGuestUserResource;
use Magento\Framework\Exception\AlreadyExistsException;

class ProductAlertStockGuestUserRepository implements ProductAlertStockGuestUserRepositoryInterface
{
    /**
     * @var ProductAlertStockGuestUserResource
     */
    private ProductAlertStockGuestUserResource $resource;

    public function __construct(ProductAlertStockGuestUserResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     * @throws AlreadyExistsException
     */
    public function save(ProductAlertStockGuestUserInterface $productAlert): ProductAlertStockGuestUserInterface
    {
        $this->resource->save($productAlert);
        return $productAlert;
    }

    public function getById(int $id): Data\ProductAlertStockGuestUserInterface
    {
        // TODO: Implement getById() method.
    }
}
