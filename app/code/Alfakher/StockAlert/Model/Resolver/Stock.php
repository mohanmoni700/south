<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Model\Resolver;

use Alfakher\StockAlert\Model\ProductAlertStockGuestUser as ProductAlertStockGuestUserFactory;
use Alfakher\StockAlert\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Alfakher\StockAlert\Model\ProductAlertStockGuestUserRepository;

class Stock implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var ProductAlertStockGuestUserFactory
     */
    protected ProductAlertStockGuestUserFactory $guestSubscriptionDataFactory;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var ProductAlertStockGuestUserRepository
     */
    private ProductAlertStockGuestUserRepository $guestUserRepository;

    /**
     * @param ProductAlertStockGuestUserFactory $guestSubscriptionDataFactory
     * @param Data $helper
     * @param ProductAlertStockGuestUserRepository $guestUserRepository
     */
    public function __construct(
        ProductAlertStockGuestUserFactory $guestSubscriptionDataFactory,
        Data $helper,
        ProductAlertStockGuestUserRepository $guestUserRepository
    ) {
        $this->guestSubscriptionDataFactory = $guestSubscriptionDataFactory;
        $this->helper = $helper;
        $this->guestUserRepository = $guestUserRepository;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
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
            $data = [
                'product_id' => $productId,
                'email_id' => $email,
                'name' => $name,
                'store_id' => $storeId,
                'website_id' => $websiteId
            ];
            $dataModel = $this->guestSubscriptionDataFactory->setData($data);
            $this->guestUserRepository->save($dataModel);
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
