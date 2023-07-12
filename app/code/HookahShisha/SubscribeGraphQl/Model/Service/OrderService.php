<?php

declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Service;

use HookahShisha\SubscribeGraphQl\Helper\Logger as SubscribeLogger;
use Magedelight\Subscribenow\Helper\Data as subscriptionHelper;
use Magedelight\Subscribenow\Logger\Logger;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory as SubscriptionFactory;
use Magedelight\Subscribenow\Model\Service\Order\Generate;
use Magedelight\Subscribenow\Model\Service\PaymentService;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;
use Magento\Eav\Model\Entity\Increment\NumericValue;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OrderService extends \Magedelight\Subscribenow\Model\Service\OrderService
{

    private SubscriptionService $subscriptionService;
    private Json $serializer;
    private SubscribeLogger $subscribeLogger;

    /**
     * @param subscriptionHelper $subscriptionHelper
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionService $subscriptionService
     * @param NumericValue $numericValue
     * @param PaymentService $paymentService
     * @param TimezoneInterface $timezone
     * @param Generate $generate
     * @param Logger $logger
     * @param EventManager $eventManager
     * @param Json $serializer
     * @param ResolverInterface $localeResolver
     * @param SubscribeLogger $subscribeLogger
     */
    public function __construct(
        subscriptionHelper $subscriptionHelper,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionService $subscriptionService,
        NumericValue $numericValue,
        PaymentService $paymentService,
        TimezoneInterface $timezone,
        Generate $generate,
        Logger $logger,
        EventManager $eventManager,
        Json $serializer,
        ResolverInterface $localeResolver,
        SubscribeLogger $subscribeLogger
    )
    {
        parent::__construct($subscriptionHelper, $subscriptionFactory, $subscriptionService, $numericValue, $paymentService, $timezone, $generate, $logger, $eventManager, $serializer, $localeResolver);
        $this->subscriptionService = $subscriptionService;
        $this->serializer = $serializer;
        $this->subscribeLogger = $subscribeLogger;
    }

    public function setOrderInfo($order, $item)
    {
        /** @var \Magento\Sales\Model\Order $order */
        parent::setOrderInfo($order, $item);
        $ip = $order->getRemoteIp();
        $this->subscribeLogger->log('IP is: ' . $ip);
        $this->getSubscriptionModel()->setData('ip_address', $ip);
        return $this;
    }

    public function setItemProductOption($order, $item)
    {
        $itemProductOptions = [];
        if ($item->getProductType() == 'bundle') {
            $bundleItemProductOptions = [];
            $product = $item->getProduct();
            $customOption = $product->getCustomOption('bundle_option_ids');
            $optionIds = $this->serializer->unserialize($customOption->getValue());
            $options = $product->getTypeInstance(true)->getOptionsByIds($optionIds, $product);
            $customOption = $product->getCustomOption('bundle_selection_ids');
            $selectionIds = $this->serializer->unserialize($customOption->getValue());
            $selections = $product->getTypeInstance(true)->getSelectionsByIds($selectionIds, $product);
            foreach ($selections->getItems() as $selection) {
                if ($selection->isSalable()) {
                    $selectionQty = $product->getCustomOption('selection_qty_' . $selection->getSelectionId());
                    if ($selectionQty) {
                        $option = $options->getItemById($selection->getOptionId());
                        if (!isset($itemProductOptions[$option->getId()])) {
                            $bundleItemProductOptions[$option->getId()] = [
                                'option_id' => $option->getId(),
                                'label' => $option->getTitle(),
                                'value' => [],
                            ];
                        }

                        $bundleItemProductOptions[$option->getId()]['value'][] = [
                            'title' => $selection->getName(),
                            'value_id' => $selection->getData('selection_id'),
                            'qty' => $selectionQty->getValue(),
                            'price' => $this->calculateDiscount($product, $selection),
                        ];
                    }
                }
            }

            if ($bundleItemProductOptions) {
                $itemProductOptions['bundle_options'] = $bundleItemProductOptions;
            }
        } else {
            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getQuoteItemId() == $item->getId()) {
                    $productOptions = $orderItem->getProductOptions();
                    if ($productOptions) {
                        unset($productOptions['info_buyRequest']);
                        $itemProductOptions = $productOptions;
                    }

                    break;
                }
            }
        }
        $this->additionalInfoData['product_options'] = $itemProductOptions;
        return $itemProductOptions;
    }

    private function calculateDiscount($product, $selection)
    {
        if ($product && $selection) {
            $selectionPrice = $selection->getFinalPrice();
            $price = $selection->getPrice();

            if (!$selectionPrice || ($selectionPrice == $price)) {
                $type = $product->getDiscountType();
                $discount = $product->getDiscountAmount();
                if ($type == 'percentage') {
                    $percentageAmount = $price * ($discount / 100);
                    $selectionPrice = $price - $percentageAmount;
                } else {
                    $selectionPrice = $price - $discount;
                }
            }
            return $this->subscriptionService->getConvertedPrice($selectionPrice);
        }
        return 0;
    }
}
