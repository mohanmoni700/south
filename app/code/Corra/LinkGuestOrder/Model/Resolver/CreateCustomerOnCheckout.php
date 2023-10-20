<?php


namespace Corra\LinkGuestOrder\Model\Resolver;

use Corra\LinkGuestOrder\Model\LinkOrder;
use Magento\Customer\Model\Customer;
use Magento\CustomerGraphQl\Model\Resolver\CreateCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CreateCustomerOnCheckout implements ResolverInterface
{
    /**
     * @var CreateCustomer $createCustomer
     */
    private CreateCustomer $createCustomer;
    /**
     * @var LinkOrder $linkOrder
     */
    private LinkOrder $linkOrder;

    /**
     * @param CreateCustomer $createCustomer
     * @param LinkOrder $linkOrder
     */
    public function __construct(
        CreateCustomer $createCustomer,
        LinkOrder $linkOrder
    ) {
        $this->createCustomer = $createCustomer;
        $this->linkOrder = $linkOrder;
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
        $result = $this->createCustomer->resolve(
            $field,
            $context,
            $info,
            $value,
            $args
        );
        if (isset($result["customer"]) &&
            isset($result["customer"]["email"]) &&
            isset($result["customer"]["model"]) &&
            isset($args["increment_id"])
        ) {
            /**
             * @var Customer $customer
             */
            $customer = $result["customer"]["model"];
            $this->linkOrder->linkOrder($result["customer"]["email"], $customer, $args["increment_id"]);
        }
        return $result;
    }
}
