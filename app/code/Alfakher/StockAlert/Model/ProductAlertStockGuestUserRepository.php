<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model;

use Alfakher\StockAlert\Api\Data\ProductAlertStockGuestUserInterface;
use Alfakher\StockAlert\Api\ProductAlertStockGuestUserRepositoryInterface;
use Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser as ProductAlertStockGuestUserResource;
use Alfakher\StockAlert\Model\ResourceModel\ProductAlertStockGuestUser\Collection as CollectionFactory;
use Alfakher\StockAlert\Model\ProductAlertStockGuestUser;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @param ProductAlertStockGuestUserResource $resource
     * @param ProductAlertStockGuestUser $productAlertStockGuestUserModel
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ProductAlertStockGuestUserResource $resource,
        ProductAlertStockGuestUser $productAlertStockGuestUserModel,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->productAlertStockGuestUserModel = $productAlertStockGuestUserModel;
        $this->collectionFactory = $collectionFactory;
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
     * @return ProductAlertStockGuestUserInterface
     */
    public function getById(int $id): ProductAlertStockGuestUserInterface
    {
        $model = $this->productAlertStockGuestUserModel->create();
        return $this->resource->load($model, $id);
    }

    /**
     * Retrieve a product alert entry by email, name, and product id.
     *
     * @param string $email
     * @param string $name
     * @param int $productId
     * @return ProductAlertStockGuestUserInterface|null
     * @throws NoSuchEntityException
     */
    public function get(string $email, string $name, int $productId): ?ProductAlertStockGuestUserInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('name', $name)
            ->addFieldToFilter('email_id', $email)
            ->addFieldToFilter('product_id', $productId);

        if ($collection->getSize() > 0) {
            return $collection->getFirstItem();
        }

        throw new NoSuchEntityException(__('Product alert entry not found.'));
    }
}
