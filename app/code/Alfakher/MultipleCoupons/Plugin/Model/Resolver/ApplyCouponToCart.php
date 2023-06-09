<?php
/**
 * @category  Alfakher
 * @package   Alfakher_MultipleCoupons
 */
declare(strict_types=1);

namespace Alfakher\MultipleCoupons\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\ApplyCouponToCart as GraphQlApplyCouponToCart;
use Mageplaza\MultipleCoupons\Helper\Data;

/**
 * @inheritdoc
 */
class ApplyCouponToCart
{

    /**
     * @var Data
     */
    private $data;

    /**
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function beforeResolve(
        GraphQlApplyCouponToCart $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($this->data->isEnabled()) {
            $requestedCoupon = $args['input']['coupon_code'];
            if(!empty($requestedCoupon)) {
                $args['input']['coupon_code'] = $this->validateCode($requestedCoupon);
            }
            if (empty($args['input']['cart_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
            }
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

            /** Validated the max number coupon can apply */
            $appliedCoupons = count($this->data->getAppliedCodes());
            $maxCouponQty = $this->data->getLimitQty($storeId);
            if ($appliedCoupons >= $maxCouponQty) {
                throw new GraphQlInputException(__('Coupon code quantity limit has been reached.'));
            }
        }
        return [$field, $context, $info, $value, $args];
    }

    /**
     * remove the duplicate coupon code
     * @param $couponCode
     * @return mixed|string
     */
    public function validateCode($couponCode)
    {
        if ($couponCode) {
            $couponArray = explode(";",$couponCode);
            $couponArray = array_unique($couponArray);
            $couponCode = implode(";", $couponArray);
        }
        return $couponCode;
    }
}
