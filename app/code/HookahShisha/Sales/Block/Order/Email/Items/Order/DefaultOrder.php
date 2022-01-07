<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Sales
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Block\Order\Email\Items\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder as SourceDefaultOrder;
use Magento\Sales\Model\Order\Item as OrderItem;
use HookahShisha\Sales\Helper\Block\AlfaBundle;

class DefaultOrder extends SourceDefaultOrder
{
    /**
     * @var AlfaBundle
     */
    private AlfaBundle $helper;

    /**
     * @param Template\Context $context
     * @param AlfaBundle $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        AlfaBundle $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
    }

    /**
     * Returns array of Item options
     *
     * @return array
     */
    public function getItemOptions(): array
    {
        $item = $this->getItem();
        $items = $this->getOrder()->getAllItems();
        $alfaBundleOptions = $this->helper->addAlfaBundleOptions($item, $items);
        $result = [];
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result[] = $options['options'];
            }
            if (isset($options['additional_options'])) {
                $result[] = $options['additional_options'];
            }
            if (isset($options['attributes_info'])) {
                $result[] = $options['attributes_info'];
            }
        }

        return array_merge(array_merge([], ...$result), $alfaBundleOptions);
    }

    /**
     * Get the html for item price
     *
     * @param OrderItem $item
     * @return string
     * @throws LocalizedException
     */
    public function getItemPrice(OrderItem $item): string
    {
        $block = $this->getLayout()->getBlock('item_price');
        $itemRowTotal = $this->getItem()->getRowTotal();
        $alfaBundle = $this->helper->getAlfaBundle($item);
        $items = $this->getOrder()->getAllItems();

        // Visually add shisha and charcoal product prices to base item totals
        if ($alfaBundle) {
            $skus = array_values($alfaBundle);

            foreach ($skus as $sku) {
                if ($sku && !is_array($sku)) {
                    $bundleItem = $this->helper->getBundleItemBySku($items, $sku);

                    $itemRowTotal += $bundleItem
                        ? (float) $bundleItem->getPrice() * (float) $bundleItem->getQtyOrdered() : 0;
                }
            }
        }

        $item->setRowTotal($itemRowTotal);
        $block->setItem($item);

        return $block->toHtml();
    }
}
