<?php
/**
 * @author  CORRA
 */
declare(strict_types=1);

namespace Corra\Veratad\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class VerificationType implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'billing', 'label' => __('Billing Only')],
            ['value' => 'shipping', 'label' => __('Shipping Only')],
            ['value' => 'both', 'label' => __('Both')],
            ['value' => 'auto_detect', 'label' => __('Auto Detect Name Difference')]
        ];
    }
}
