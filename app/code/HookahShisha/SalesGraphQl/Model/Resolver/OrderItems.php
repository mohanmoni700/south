<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_SalesGraphQl
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider as OrderItemProvider;
use Magento\SalesGraphQl\Model\Resolver\OrderItems as SourceOrderItems;

class OrderItems extends SourceOrderItems
{
    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @var OrderItemProvider
     */
    private OrderItemProvider $orderItemProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param OrderItemProvider $orderItemProvider
     */
    public function __construct(ValueFactory $valueFactory, OrderItemProvider $orderItemProvider)
    {
        parent::__construct($valueFactory, $orderItemProvider);

        $this->valueFactory = $valueFactory;
        $this->orderItemProvider = $orderItemProvider;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $parentOrder */
        $parentOrder = $value['model'];

        $orderItemIds = [];
        foreach ($parentOrder->getItems() as $item) {
            // Return only order items which are not part of Alfa Bundle
            if (!$item->getParentItemId() && !$item->getParentAlfaBundle()) {
                $orderItemIds[] = (int)$item->getItemId();
            }
            $this->orderItemProvider->addOrderItemId((int)$item->getItemId());
        }

        $itemsList = [];
        foreach ($orderItemIds as $orderItemId) {
            $itemsList[] = $this->valueFactory->create(
                function () use ($orderItemId) {
                    return $this->orderItemProvider->getOrderItemById((int)$orderItemId);
                }
            );
        }

        return $itemsList;
    }
}
