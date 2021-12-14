<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Sales
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Block\Order\Email\Items;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Order\Email\Items\DefaultItems as SourceDefaultItems;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use HookahShisha\Sales\Helper\Block\AlfaBundle;

class DefaultItems extends SourceDefaultItems
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
     * Returns Items options as array
     *
     * @return array
     */
    public function getItemOptions(): array
    {
        $item = $this->getItem()->getOrderItem();
        $items = $this->getOrder()->getAllItems();
        $alfaBundleOptions = $this->helper->addAlfaBundleOptions($item, $items);
        $result = [];

        if ($options = $this->getItem()->getOrderItem()->getProductOptions()) {
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
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return string
     * @throws LocalizedException
     */
    public function getItemPrice($item): string
    {
        $block = $this->getLayout()->getBlock('item_price');
        $itemRowTotal = (float) $item->getPrice() * (float) $this->getItem()->getQty();
        $itemBaseRowTotal = (float) $item->getBasePrice() * (float) $this->getItem()->getQty();
        $alfaBundle = $this->helper->getAlfaBundle($item);
        $items = $this->getOrder()->getAllItems();

        // Visually add shisha and charcoal product prices to base item totals
        if ($alfaBundle) {
            $skus = array_values($alfaBundle);

            foreach ($skus as $sku) {
                if ($sku) {
                    $bundleItem = $this->helper->getBundleItemBySku($items, $sku);

                    $itemRowTotal += $bundleItem
                        ? (float) $bundleItem->getPrice() * (float) $bundleItem->getQtyOrdered() : 0;
                    $itemBaseRowTotal += $bundleItem ?
                        (float) $bundleItem->getBasePrice() * (float) $bundleItem->getQtyOrdered() : 0;
                }
            }
        }

        $item->setRowTotal($itemRowTotal);
        $item->setBaseRowTotal($itemBaseRowTotal);
        $block->setItem($item);

        return $block->toHtml();
    }
}
