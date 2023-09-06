<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model\Resolver;

use Alfakher\StockAlert\Model\ProductAlertStockGuestUserFactory;
use Alfakher\StockAlert\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class Stock implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    protected ProductAlertStockGuestUserFactory $guestSubscriptionDataFactory;

    public function __construct(
        ProductAlertStockGuestUserFactory $guestSubscriptionDataFactory,
        Data $helper
    ) {
        $this->guestSubscriptionDataFactory = $guestSubscriptionDataFactory;
        $this->helper = $helper;
    }

    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $result = ['message' => '', 'id' => null];
        try {
            // Fetch data from args
            $productId = $args['input']['product_id'];
            $email = $args['input']['email'];
            $name = $args['input']['name'];

            $storeId = $this->helper->getStoreId();
            $websiteId = $this->helper->getWebsiteId();
            $dataModel = $this->guestSubscriptionDataFactory->create();
            $dataModel->setProductId($productId);
            $dataModel->setEmailId($email);
            $dataModel->setName($name);
            $dataModel->setStoreId($storeId);
            $dataModel->setWebsiteId($websiteId);
            $dataModel->save();

            // Update the result with success message and ID
            $result['message'] = 'Subscription added successfully';
            $result['id'] = $dataModel->getId();
        } catch (LocalizedException $e) {
            // Handle exception and update the result with an error message
            $result['message'] = $e->getMessage();
        }

        return $result;
    }
}
