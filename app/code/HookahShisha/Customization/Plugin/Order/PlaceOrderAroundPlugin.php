<?php
namespace HookahShisha\Customization\Plugin\Order;

use Magento\Backend\Model\Auth\Session;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;

/**
 * Add comment for order history.
 */
class PlaceOrderAroundPlugin
{
    /**
     * @param Session $authSession
     */
    public function __construct(
        Session $authSession
    ) {
        $this->authSession = $authSession;
    }
    /**
     * Around addStatusHistoryComment.
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param \Closure $proceed
     * @param string $comment
     * @param bool|string $status [optional]
     * @return OrderStatusHistoryInterface
     */
    public function aroundAddStatusHistoryComment(
        \Magento\Sales\Model\Order $subject,
        $proceed,
        $comment,
        $status = false
    ) {
        $adminUser = $this->authSession->getUser()->getUsername();
        if ($comment) {
            $comment = $comment . " Commented By " . $adminUser;
        }

        $result = $proceed($comment, $status);
        return $result;
    }
}
