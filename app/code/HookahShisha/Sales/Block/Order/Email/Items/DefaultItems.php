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
use HookahShisha\Sales\Helper\Block\AlfaBundle;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;

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
        $items = $this->getOrder()->getAllItems();
        $superPackItemPrice = $this->helper->getSuperPackItemPrice($item, $items);
        $price = $superPackItemPrice ?: $item->getPrice();

        $item->setRowTotal((float) $price * (float) $this->getItem()->getQty());
        $item->setBaseRowTotal((float) $price * (float) $this->getItem()->getQty());

        $block->setItem($item);

        return $block->toHtml();
    }
}
