<?php
namespace HookahShisha\OrderGraphQl\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as MagentoPlaceOrder;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterfaceFactory as OrderRepositoryInterfaceFactory;
use Corra\Veratad\Model\VeratadProcessOrder;

/**
 * This plugin validates and saves the order attribute and disable ageverification for specific stores.
 */
class VaratedPlaceOrder
{
    /**
     * @var OrderInterfaceFactory
     */
    private OrderInterfaceFactory $orderFactory;

    /**
     * @var OrderRepositoryInterfaceFactory
     */
    private OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory;
    /**
     * @var VeratadProcessOrder
     */
    private $veratadProcessOrder;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory
     * @param VeratadProcessOrder $veratadProcessOrder
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        OrderRepositoryInterfaceFactory $orderRepositoryInterfaceFactory,
        VeratadProcessOrder $veratadProcessOrder
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderRepositoryInterfaceFactory = $orderRepositoryInterfaceFactory;
        $this->veratadProcessOrder = $veratadProcessOrder;
    }

    private const DISABLED_AGEVERIFICATION_STORES = [
        'ooka_de_store_en',
        'ooka_de_store_de',
        'ooka_uae_store_en',
        'ooka_uae_store_ar',
        'hookah_store_view_en',
        'hookah_store_view_es',
        'hookah_store_view_de',
        'hookah_wholesalers_store_view'
    ];

    /**
     *  Validate 'veratad_dob' before placing order.
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
        MagentoPlaceOrder $subject, // NOSONAR
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null, // NOSONAR
        array $args = null // NOSONAR
    ) {
        $store = $context->getExtensionAttributes()->getStore();

        if ((
            empty($args['input']['veratad_dob']) ||
            !$args['input']['veratad_dob']) &&
            !in_array($store->getCode(), self::DISABLED_AGEVERIFICATION_STORES)
        ) {
            throw new GraphQlInputException(
                __('Required parameter "veratad_dob" is missing')
            );
        }
    }

    /**
     * Save 'veratad_dob' value when order is placed.
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
        MagentoPlaceOrder $subject, // NOSONAR
        $return,
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null, // NOSONAR
        array $args = null // NOSONAR
    ) {
        $store = $context->getExtensionAttributes()->getStore();
        if (in_array($store->getCode(), self::DISABLED_AGEVERIFICATION_STORES)) {
            return $return;
        }
        $orderModel = $this->orderFactory->create();
        $order = $orderModel->loadByIncrementId($return['order']['order_number'] ?? '');
        if ($order) {
            list($response, $billingAddressVerificationStatus, $shippingAddressVerificationStatus, $veratadDetails) =
                $this->veratadProcessOrder->handleVeratadPlaceOrder(
                    $context,
                    $order,
                    $args['input']['veratad_dob'],
                    $args['input']['is_ageverified']
                );
            //Update the Ageverification Response to SalesOrderExtended Table
            $this->veratadProcessOrder->saveAgeVeratedOrderInfo(
                $order,
                $args['input']['veratad_dob'],
                $response,
                $billingAddressVerificationStatus,
                $shippingAddressVerificationStatus,
                $veratadDetails
            );
            //change the order status only if ageverification failed
            if (!$response) {
                $orderResponse =  $this->veratadProcessOrder->changeOrderStatus($order);
                $this->orderRepositoryInterfaceFactory->create()->save($orderResponse);
            }
        }
        return $return;
    }
}
