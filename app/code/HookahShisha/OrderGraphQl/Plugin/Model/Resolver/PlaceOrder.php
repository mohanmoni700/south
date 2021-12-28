<?php
declare(strict_types=1);

namespace HookahShisha\OrderGraphQl\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as MagentoPlaceOrder;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory as OrderRepositoryInterfaceFactory;

/**
 * This plugin validates and saves the order attribute
 */
class PlaceOrder
{
    private OrderInterfaceFactory $orderFactory;
    private OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderRepositoryInterfaceFactory = $orderRepositoryInterfaceFactory;
    }

    /**
     *  Validate 'alfa_consent' before placing order.
     *
     * @param MagentoPlaceOrder $subject
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws GraphQlInputException
     */
    public function beforeResolve(
        MagentoPlaceOrder $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['input']['alfa_consent']) || !$args['input']['alfa_consent']) {
            throw new GraphQlInputException(
                __('Required parameter "alfa_consent" is missing / "alfa_consent" should be true to place the order ')
            );
        }
    }

    /**
     * Save 'alfa_consent' value when order is placed.
     *
     * @param MagentoPlaceOrder $subject
     * @param array $return
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        MagentoPlaceOrder $subject,
        $return,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $orderFactory = $this->orderFactory->create();
        $order = $orderFactory->loadByIncrementId($return['order']['order_number'] ?? '');
        if ($order) {
            $order->setData('alfa_consent', true);
            $this->orderRepositoryInterfaceFactory->create()->save($order);
        }
        return $return;
    }
}
