<?php
/**
 * @category  Alfakher
 * @package   Alfakher_MultipleCoupons
 */
declare(strict_types=1);

namespace Alfakher\MultipleCoupons\Plugin\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
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
    public function aroundResolve(
        GraphQlApplyCouponToCart $subject,
        callable $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        if ($this->data->isEnabled($storeId)) {
            $couponQty = 0;
            $requestedCoupon = strtolower($args['input']['coupon_code']);
            if(!empty($requestedCoupon)) {
                $couponArray = $this->validateCode($requestedCoupon);
                $couponQty = count($couponArray);
                $this->validateOneCoupon($couponArray);
                $args['input']['coupon_code'] = implode(";", $couponArray);
            }
            if (empty($args['input']['cart_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
            }

            /** Validated the max number coupon can apply */
            $maxCouponQty = $this->data->getLimitQty($storeId);
            if ($couponQty > $maxCouponQty) {
                throw new GraphQlInputException(__('Coupon code quantity limit has been reached.'));
            }
        }
        try {
            $result = $proceed($field, $context, $info, $value, $args);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $result;
    }

    /**
     * @param $couponArray
     * @return void
     * @throws GraphQlInputException
     */
    public function validateOneCoupon($couponArray){
        $magentoCoupon = 0;
        $yotpoCoupon = 0;
        foreach ($couponArray as $coupon) {
            if (str_contains($coupon, 'loyalty')) {
                $yotpoCoupon++;
            } else {
                $magentoCoupon++;
            }
        }
        if ($yotpoCoupon > 1 || $magentoCoupon > 1) {
            throw new GraphQlInputException(__('Coupon code quantity limit has been reached.'));
        }

    }

    /**
     * remove the duplicate coupon code
     * @param $couponCode
     * @return mixed|string
     */
    public function validateCode($couponCode)
    {
        if ($couponCode) {
            $couponArray = explode(";", $couponCode);
            $qtyBefore = count($couponArray);
            $couponArray = array_unique($couponArray);
            $qtyAfter = count($couponArray);
            if ($qtyBefore != $qtyAfter) {
                throw new GraphQlInputException(__('Coupon code is already applied.'));
            }
            return $couponArray;
        }
    }
}
