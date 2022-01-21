<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Sales
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Block\Order\Email\Items;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Order\Email\Items\DefaultItems as SourceDefaultItems;
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
}
