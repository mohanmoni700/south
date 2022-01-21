<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Sales
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Block\Order\Email\Items\Order;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder as SourceDefaultOrder;
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
}
