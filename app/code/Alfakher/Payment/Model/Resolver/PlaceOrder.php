<?php
declare(strict_types=1);

namespace Alfakher\Payment\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as ParentPlaceOrder;

class PlaceOrder
{
    /**
     * @param \Magento\QuoteGraphQl\Model\Resolver\PlaceOrder $subject
     * @param \Closure $proceed
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     * @throws GraphQlInputException
     */
    public function aroundResolve(
        ParentPlaceOrder $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $proceed($field, $context, $info, $value, $args);
        } catch (LocalizedException $e) {
            if ($e->getMessage() == 'Unable to place order: The payment method you requested is not available.') {
                throw new GraphQlInputException(__('Your credit card has been redacted. Please re-enter your credit card details, click “Add a new card”.'));
            } else {
                throw new GraphQlInputException(__('%message', ['message' => $e->getMessage()]), $e);
            }
        }
    }
}
