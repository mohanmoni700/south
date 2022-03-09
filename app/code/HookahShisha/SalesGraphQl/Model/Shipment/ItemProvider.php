<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_SalesGraphQl
 * @author    CORRA
 */

namespace HookahShisha\SalesGraphQl\Model\Shipment;

use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\SalesGraphQl\Model\Shipment\Item\FormatterInterface;
use Magento\SalesGraphQl\Model\Shipment\ItemProvider as SourceItemProvider;

class ItemProvider extends SourceItemProvider
{
    /**
     * @var FormatterInterface[]
     */
    private array $formatters;

    /**
     * @param array $formatters
     */
    public function __construct(array $formatters = [])
    {
        parent::__construct($formatters);

        $this->formatters = $formatters;
    }

    /**
     * Get item data for shipment
     *
     * @param ShipmentInterface $shipment
     * @return array
     */
    public function getItemData(ShipmentInterface $shipment): array
    {
        $shipmentItems = [];

        foreach ($shipment->getItems() as $shipmentItem) {
            if ($shipmentItem->getOrderItem()->getParentAlfaBundle()) {
                continue;
            }

            $formattedItem = $this->formatItem($shipment, $shipmentItem);
            if ($formattedItem) {
                $shipmentItems[] = $formattedItem;
            }
        }
        return $shipmentItems;
    }

    /**
     * Format individual shipment item
     *
     * @param ShipmentInterface $shipment
     * @param ShipmentItemInterface $shipmentItem
     * @return array|null
     */
    private function formatItem(ShipmentInterface $shipment, ShipmentItemInterface $shipmentItem): ?array
    {
        $orderItem = $shipmentItem->getOrderItem();
        $formatter = $this->formatters[$orderItem->getProductType()] ?? $this->formatters['default'];

        return $formatter->formatShipmentItem($shipment, $shipmentItem);
    }
}
