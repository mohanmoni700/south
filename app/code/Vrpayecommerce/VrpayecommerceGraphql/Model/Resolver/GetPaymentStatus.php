<?php
declare (strict_types = 1);

namespace Vrpayecommerce\VrpayecommerceGraphql\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vrpayecommerce\Vrpayecommerce\Controller\Payment\Response;
use Vrpayecommerce\VrpayecommerceGraphql\Helper\Payment;

class GetPaymentStatus implements ResolverInterface
{
    /**
     * @var Response
     */
    private Response $response;

    /**
     * @var Payment
     */
    private Payment $helperPayment;

    /**
     * @param Response $response
     * @param Payment $helperPayment
     */
    public function __construct(
        Response $response,
        Payment $helperPayment
    ) {
        $this->response = $response;
        $this->helperPayment = $helperPayment;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $customerId = $context->getUserId();

        $checkoutId = $args['input']['checkoutId'];
        $paymentMethod = $args['input']['paymentMethod'];
        $orderIncrementId = $args['input']['orderIncrementId'];

        return $this->helperPayment
            ->processPayment($paymentMethod, $checkoutId, $orderIncrementId, $customerId);
    }
}
