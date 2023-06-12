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
use Magento\Framework\Registry;

/**
 * @inheritdoc
 */
class ApplyCouponToCart
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Data
     */
    private $data;

    /**
     * @param Data $data
     * @param Registry $registry
     */
    public function __construct(
        Data $data,
        Registry $registry
    ) {
        $this->data = $data;
        $this->registry = $registry;
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
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        if ($this->data->isEnabled($storeId)) {
            /** Core file its sets only if extension is enable for default config, We need it in website level */
            $this->registry->register('is_multiple_coupon', true);
            
            $couponQty = 0;
            $requestedCoupon = $args['input']['coupon_code'];
            if(!empty($requestedCoupon)) {
                $couponArray = $this->validateCode($requestedCoupon);
                $couponQty = count($couponArray);
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
            $couponArray = explode(";", $couponCode);
            return array_unique($couponArray);
        }
    }
}
