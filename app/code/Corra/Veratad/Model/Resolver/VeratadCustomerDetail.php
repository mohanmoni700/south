<?php
declare(strict_types=1);

namespace Corra\Veratad\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Get Veratad Customer Identification detail
 */
class VeratadCustomerDetail implements ResolverInterface
{
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var CustomerInterface $customer */
        $customer = $value['model'];
        $veratadData = [];
        $customAttributes = $customer->getCustomAttributes();
        if (count($customAttributes)) {
            if (!empty($customer->getCustomAttribute("is_ageverified"))) {
                $veratadData['is_ageverified'] =
                    (bool)$customer->getCustomAttribute("is_ageverified")->getValue();
            }
            if (!empty($customer->getCustomAttribute("age_verification_override"))) {
                $veratadData['age_verification_override'] =
                    (bool)$customer->getCustomAttribute("age_verification_override")->getValue();
            }
        }
        return $veratadData;
    }
}
