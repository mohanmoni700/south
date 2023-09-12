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

    /**
     * @var ProductAlertStockGuestUser
     */
    private ProductAlertStockGuestUser $productAlertStockGuestUserModel;

    /**
     * @param ProductAlertStockGuestUserResource $resource
     * @param ProductAlertStockGuestUser $productAlertStockGuestUserModel
     */
    public function __construct(
        ProductAlertStockGuestUserResource $resource,
        ProductAlertStockGuestUser $productAlertStockGuestUserModel
    ) {
        $this->resource = $resource;
        $this->productAlertStockGuestUserModel = $productAlertStockGuestUserModel;
    }

    /**
     * @inheritdoc
     *
     * @throws AlreadyExistsException
     */
    public function save(ProductAlertStockGuestUserInterface $productAlert): ProductAlertStockGuestUserInterface
    {
        $this->resource->save($productAlert);
        return $productAlert;
    }

    /**
     * Get data by id
     *
     * @param int $id
     * @return ProductAlertStockGuestUser
     */
    public function getById(int $id): ProductAlertStockGuestUser
    {
        return $this->productAlertStockGuestUserModel->load($id);
    }
}
