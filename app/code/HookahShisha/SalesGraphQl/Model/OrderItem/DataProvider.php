<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_SalesGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\SalesGraphQl\Model\OrderItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider as SourceDataProvider;
use Magento\SalesGraphQl\Model\OrderItem\OptionsProcessor;

/**
 * Data provider for order items
 */
class DataProvider extends SourceDataProvider
{
    /**
     * @var OrderItemRepositoryInterface
     */
    private OrderItemRepositoryInterface $orderItemRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var OptionsProcessor
     */
    private OptionsProcessor $optionsProcessor;

    /**
     * @var int[]
     */
    private array $orderItemIds = [];

    /**
     * @var array
     */
    private array $orderItemList = [];

    /**
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ProductRepositoryInterface $productRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OptionsProcessor $optionsProcessor
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OptionsProcessor $optionsProcessor
    ) {
        parent::__construct(
            $orderItemRepository,
            $productRepository,
            $orderRepository,
            $searchCriteriaBuilder,
            $optionsProcessor
        );

        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->optionsProcessor = $optionsProcessor;
    }

    /**
     * Add order item id to list for fetching
     *
     * @param int $orderItemId
     */
    public function addOrderItemId(int $orderItemId): void
    {
        if (!in_array($orderItemId, $this->orderItemIds)) {
            $this->orderItemList = [];
            $this->orderItemIds[] = $orderItemId;
        }
    }

    /**
     * Get order item by item id
     *
     * @param int $orderItemId
     * @return array
     */
    public function getOrderItemById(int $orderItemId): array
    {
        $orderItems = $this->fetch();
        if (!isset($orderItems[$orderItemId])) {
            return [];
        }
        return $orderItems[$orderItemId];
    }

    /**
     * Fetch order items and return in format for GraphQl
     *
     * @return array
     */
    private function fetch(): array
    {
        if (empty($this->orderItemIds) || !empty($this->orderItemList)) {
            return $this->orderItemList;
        }

        $itemSearchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderItemInterface::ITEM_ID, $this->orderItemIds, 'in')
            ->create();

        $orderItems = $this->orderItemRepository->getList($itemSearchCriteria)->getItems();
        $productList = $this->fetchProducts($orderItems);
        $orderList = $this->fetchOrders($orderItems);

        foreach ($orderItems as $orderItem) {
            /** @var ProductInterface $associatedProduct */
            $associatedProduct = $productList[$orderItem->getProductId()] ?? null;
            $associatedProductType = $associatedProduct ? $associatedProduct->getTypeId() : '';
                /** @var OrderInterface $associatedOrder */
            $associatedOrder = $orderList[$orderItem->getOrderId()];
            $itemOptions = $this->optionsProcessor->getItemOptions($orderItem);
            $shishaTitle = $associatedProduct && $associatedProductType == 'configurable'
                ? $associatedProduct->getShishaTitle() : '';
            $charcoalTitle = $associatedProduct && $associatedProductType == 'configurable'
                ? $associatedProduct->getCharcoalTitle() : '';
            $alfaBundle = $orderItem->getAlfaBundle();

            $shishaSku = '';
            $charcoalSku = '';
            $superPack = '';
            if ($alfaBundle) {
                $alfaBundle = json_decode($alfaBundle, true);
                $shishaSku = $alfaBundle['shisha_sku'];
                $charcoalSku = $alfaBundle['charcoal_sku'];
                $superPack = $alfaBundle['super_pack_flavours'] ?? null;
            }

            $shishaItem = $this->getBundleItem($orderItems, $shishaSku);
            $charcoalItem = $this->getBundleItem($orderItems, $charcoalSku);
            $shishaPrice = $shishaItem ? $shishaItem->getPrice() : 0;
            $charcaolPrice = $charcoalItem ? $charcoalItem->getPrice() : 0;

            $this->orderItemList[$orderItem->getItemId()] = [
                'id' => base64_encode($orderItem->getItemId()),
                'associatedProduct' => $associatedProduct,
                'model' => $orderItem,
                'product_name' => $orderItem->getName(),
                'product_sku' => $orderItem->getSku(),
                'product_url_key' => $associatedProduct ? $associatedProduct->getUrlKey() : null,
                'product_type' => $orderItem->getProductType(),
                'status' => $orderItem->getStatus(),
                'discounts' => $this->getDiscountDetails($associatedOrder, $orderItem),
                'product_sale_price' => [
                    'value' => $orderItem->getPrice() + $shishaPrice + $charcaolPrice,
                    'currency' => $associatedOrder->getOrderCurrencyCode()
                ],
                'selected_options' => $itemOptions['selected_options'],
                'entered_options' => $itemOptions['entered_options'],
                'quantity_ordered' => $orderItem->getQtyOrdered(),
                'quantity_shipped' => $orderItem->getQtyShipped(),
                'quantity_refunded' => $orderItem->getQtyRefunded(),
                'quantity_invoiced' => $orderItem->getQtyInvoiced(),
                'quantity_canceled' => $orderItem->getQtyCanceled(),
                'quantity_returned' => $orderItem->getQtyReturned(),
                'shisha_title' => $shishaTitle,
                'charcoal_title' => $charcoalTitle,
                'alfa_bundle_flavour' => $shishaItem ? $shishaItem->getProduct()->getAttributeText('flavour') : '',
                'alfa_bundle_charcoal' => $charcoalItem
                    ? $charcoalItem->getProduct()->getName()
                    . ': '
                    . $charcoalItem->getProduct()->getCharcoalShortDetail() : '',
                'super_pack_flavour' => is_array($superPack) ? $superPack : []
            ];
        }

        return $this->orderItemList;
    }

    /**
     * Fetch associated order for order items
     *
     * @param array $orderItems
     * @return array
     */
    private function fetchOrders(array $orderItems): array
    {
        $orderIds = array_map(
            function ($orderItem) {
                return $orderItem->getOrderId();
            },
            $orderItems
        );

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $orderIds, 'in')
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        $orderList = [];
        foreach ($orders as $order) {
            $orderList[$order->getEntityId()] = $order;
        }
        return $orderList;
    }

    /**
     * Fetch associated products for order items
     *
     * @param array $orderItems
     * @return array
     */
    private function fetchProducts(array $orderItems): array
    {
        $productIds = array_map(
            function ($orderItem) {
                return $orderItem->getProductId();
            },
            $orderItems
        );

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();
        $productList = [];
        foreach ($products as $product) {
            $productList[$product->getId()] = $product;
        }
        return $productList;
    }

    /**
     * Returns information about an applied discount
     *
     * @param OrderInterface $associatedOrder
     * @param OrderItemInterface $orderItem
     * @return array
     */
    private function getDiscountDetails(OrderInterface $associatedOrder, OrderItemInterface $orderItem) : array
    {
        if ($associatedOrder->getDiscountDescription() === null && $orderItem->getDiscountAmount() == 0
            && $associatedOrder->getDiscountAmount() == 0
        ) {
            $discounts = [];
        } else {
            $discounts [] = [
                'label' => $associatedOrder->getDiscountDescription() ?? __('Discount'),
                'amount' => [
                    'value' => abs($orderItem->getDiscountAmount()) ?? 0,
                    'currency' => $associatedOrder->getOrderCurrencyCode()
                ]
            ];
        }
        return $discounts;
    }

    /**
     * Returns product if its part of alfa bundle
     *
     * @param array $orderItems
     * @param string $sku
     * @return false|mixed
     */
    private function getBundleItem(array $orderItems, string $sku)
    {
        if (!$sku) {
            return false;
        }

        foreach ($orderItems as $item) {
            if ($item->getSku() == $sku) {
                return $item;
            }
        }

        return false;
    }
}
