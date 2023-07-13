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
        if ($this->authSession->getUser()) {
            $adminUser = $this->authSession->getUser()->getUsername();
        } else {
            $adminUser = null;
        }
        $result = $proceed($comment, $status);
        if ($comment && $adminUser) {
            $result->setAdminName($adminUser);
        }
        return $result;
    }
}
