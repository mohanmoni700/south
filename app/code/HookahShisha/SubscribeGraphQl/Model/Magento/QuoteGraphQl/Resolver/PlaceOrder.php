<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Magento\QuoteGraphQl\Resolver;

use Magedelight\Subscribenow\Model\ResourceModel\ProductAssociatedOrders\CollectionFactory as AssociatedOrders;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as MagentoPlaceOrder;

/**
 * PlaceOrder
 */
class PlaceOrder
{
    /**
     * @var AssociatedOrders $associatedOrdersFactory
     */
    protected AssociatedOrders $associatedOrdersFactory;

    /**
     * @param AssociatedOrders $associatedOrdersFactory
     */
    public function __construct(
        AssociatedOrders $associatedOrdersFactory
    ) {
        $this->associatedOrdersFactory = $associatedOrdersFactory;
    }

    /**
     * Set subscription details
     *
     * @param MagentoPlaceOrder $subject
     * @param array $return
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        MagentoPlaceOrder $subject,
        $return,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $incrementId = $return['order']['order_number'] ?? '';
        if ($incrementId) {
            $profiles = $this->getSubscriptionProfiles($incrementId);
            if ($profiles->getSize()) {
                foreach ($profiles as $profile) {
                    $return['order']['orderSubscriptionDetails'][] = [
                        'profile_id' => $profile->getProfileId(),
                        'subscription_id' => $profile->getSubscriptionId(),
                    ];
                }
            }
        }
        return $return;
    }

    /**
     * Get subscription profiles
     *
     * @param int $incrementId
     * @return mixed
     */
    public function getSubscriptionProfiles($incrementId)
    {
        $orderId = $incrementId;
        $collection = $this->associatedOrdersFactory->create();
        $collection->addFieldToFilter('order_id', $orderId);
        $collection->getSelect()->join(
            ['subscriber' => $collection->getTable('md_subscribenow_product_subscribers')],
            'subscriber.subscription_id = main_table.subscription_id',
            ['subscription_id', 'profile_id']
        );
        return $collection;
    }
}
