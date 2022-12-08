<?php
declare (strict_types = 1);

namespace Vrpayecommerce\VrpayecommerceGraphql\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vrpayecommerce\VrpayecommerceGraphql\Helper\Payment;

class DeleteVrSavedCard implements ResolverInterface
{
    /**
     * @var Payment
     */
    private Payment $paymentHelper;

    /**
     * @param Payment $payment
     */
    public function __construct(
        Payment $payment
    ) {
        $this->paymentHelper = $payment;
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
        if (empty($customerId)) {
            throw new GraphQlAuthorizationException(__('Denied access to saved cards'));
        }
        if (!isset($args['paymentMethodType']) || !isset($args['informationId'])) {
            throw new GraphQlInputException(__('Please add all the fields'));
        }
        return $this->paymentHelper
            ->deleteSavedCard($customerId, $args['paymentMethodType'], $args['informationId']);
    }
}
