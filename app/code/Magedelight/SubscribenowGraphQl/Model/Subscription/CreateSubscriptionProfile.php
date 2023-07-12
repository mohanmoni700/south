<?php

namespace Magedelight\SubscribenowGraphQl\Model\Subscription;

use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface;
use Magedelight\Subscribenow\Api\Data\ProductSubscribersInterfaceFactory;
use Magedelight\Subscribenow\Api\ProductSubscribersRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Reflection\DataObjectProcessor;

class CreateSubscriptionProfile
{
    /**
     * @var ProductSubscribersRepositoryInterface
     */
    private $productSubscribersRepository;
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var ProductSubscribersInterfaceFactory
     */
    private $productSubscriber;

    public function __construct(
        ProductSubscribersRepositoryInterface $productSubscribersRepository,
        ProductSubscribersInterfaceFactory $productSubscriber,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper
    )
    {
        $this->productSubscribersRepository = $productSubscribersRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->productSubscriber = $productSubscriber;
    }

    /**
     * @param array $data
     * @return ProductSubscribersInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data, $customerId)
    {
        try {
            $subscription = $this->createSubscription($data, $customerId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $subscription;
    }

    /**
     * @param $data
     * @return ProductSubscribersInterface
     * @throws LocalizedException
     */
    public function createSubscription($data, $customerId)
    {
        $subscriptionDataObject = $this->productSubscriber->create();
        $requiredDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
            $subscriptionDataObject,
            ProductSubscribersInterface::class
        );
        $additionalInfo = [];
        if (isset($data['additional_info'])) {
            if (isset($data['additional_info'][0]['product_sku']) && isset($data['additional_info'][0]['shipping_title'])) {
                $additionalInfo['product_sku'] = $data['additional_info'][0]['product_sku'];
                $additionalInfo['shipping_title'] = $data['additional_info'][0]['shipping_title'];
            }
            unset($data['additional_info']);
        }
        $orderInfo = [];
        if (isset($data['order_item_info']) && isset($data['order_item_info'][0]['qty']) && isset($data['order_item_info'][0]['subscription_start_date'])) {
            if (isset($data['order_item_info'][0]['options'][0]['_1'])) {
                $orderInfo['options']['_1'] = $data['order_item_info'][0]['options'][0]['_1'];
                $orderInfo['qty'] = $data['order_item_info'][0]['qty'];
                $orderInfo['subscription_start_date'] = $data['order_item_info'][0]['subscription_start_date'];
            }
            unset($data['order_item_info']);
        }

        $data = array_merge($requiredDataAttributes, $data);
        $this->dataObjectHelper->populateWithArray(
            $subscriptionDataObject,
            $data,
            ProductSubscribersInterface::class
        );
        $subscriptionDataObject->setAdditionalInfo($additionalInfo);
        $subscriptionDataObject->setOrderInfo($orderInfo);
        $subscriptionDataObject->setCustomerId($customerId);
        return $this->productSubscribersRepository->save($subscriptionDataObject);
    }
}
