<?php
/**
 * @category  Alfakher
 * @package   Alfakher_MultipleCoupons
 */
declare(strict_types=1);

namespace Alfakher\MultipleCoupons\Plugin\Model\Resolver;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Resolver\ApplyCouponToCart as GraphQlApplyCouponToCart;
use Mageplaza\MultipleCoupons\Helper\Data;

/**
 * @inheritdoc
 */
class ApplyCouponToCart
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @var Data
     */
    private $data;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CouponManagementInterface $couponManagement
     * @param Data $data
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CouponManagementInterface $couponManagement,
        Data $data
    ) {
        $this->getCartForUser   = $getCartForUser;
        $this->couponManagement = $couponManagement;
        $this->data             = $data;
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

            if (empty($args['input']['cart_id'])) {
                throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
            }
            $maskedCartId = $args['input']['cart_id'];

            $currentUserId = $context->getUserId();
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
            $cartId = $cart->getId();

            /** Validated the max number coupon can apply */
            $appliedCoupons = count($this->data->getAppliedCodes());
            $maxCouponQty = $this->data->getLimitQty($storeId);
            if ($appliedCoupons >= $maxCouponQty) {
                throw new GraphQlInputException(__('Coupon code quantity limit has been reached.'));
            }
            /** Remove all coupon before applying new Loyalty,
             * At a time only one Loyalty coupon should be there on cart **/
            try {
                $this->removeCouponsFromCart($cartId);
            } catch (LocalizedException $e) {
                throw new GraphQlInputException(__("something went wrong". $e->getMessage()));
            }
        }
        return [$field, $context, $info, $value, $args];
    }

    /**
     * Remove Coupons from Cart
     * @param $cartId
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     */
    public function removeCouponsFromCart($cartId)
    {
        try {
            $this->couponManagement->remove($cartId);
        } catch (NoSuchEntityException $e) {
            $message = $e->getMessage();
            if (preg_match('/The "\d+" Cart doesn\'t contain products/', $message)) {
                $message = 'Cart does not contain products';
            }
            throw new GraphQlNoSuchEntityException(__($message), $e);
        } catch (CouldNotDeleteException $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
