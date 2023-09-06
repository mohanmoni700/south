<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model\Resolver;

use Alfakher\StockAlert\Model\ProductAlertStockGuestUserFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductAlertStatus implements ResolverInterface
{
    protected ProductAlertStockGuestUserFactory $guestSubscriptionFactory;

    public function __construct(
        ProductAlertStockGuestUserFactory $guestSubscriptionFactory
    ) {
        $this->guestSubscriptionFactory = $guestSubscriptionFactory;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return string[]
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $email = $args['input']['email'];
        $name = $args['input']['name'];
        $productId = $args['input']['product_id'];

        // Load the product alert entry using email, name, and product id
        $guestSubscriptionModel = $this->guestSubscriptionFactory->create();
        $collection = $guestSubscriptionModel->getCollection();
        $collection->addFieldToFilter('name', $name)
            ->addFieldToFilter('email_id', $email)
            ->addFieldToFilter('product_id', $productId);

        // Check if any record exists with the given criteria
        $status = $collection->getSize() > 0 ? 'You are already Subscribed' : 'Not Subscribed';

        return ['status' => $status];
    }
}
