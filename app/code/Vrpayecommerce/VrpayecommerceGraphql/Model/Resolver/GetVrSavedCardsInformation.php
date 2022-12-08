<?php
declare (strict_types = 1);

namespace Vrpayecommerce\VrpayecommerceGraphql\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vrpayecommerce\Vrpayecommerce\Model\Payment\Information;
use Vrpayecommerce\VrpayecommerceGraphql\Helper\Payment;

class GetVrSavedCardsInformation implements ResolverInterface
{
    /**
     * @var Information
     */
    private Information $information;
    /**
     * @var Payment
     */
    private Payment $paymentHelper;

    /**
     * @param Information $information
     * @param Payment $payment
     */
    public function __construct(
        Information $information,
        Payment $payment
    ) {
        $this->information = $information;
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

        return $this->paymentHelper->getSavedCards($customerId);
    }
}
