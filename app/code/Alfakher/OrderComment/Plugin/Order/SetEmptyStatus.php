<?php

namespace Alfakher\OrderComment\Plugin\Order;

use Magento\Sales\Block\Adminhtml\Order\View\History;

class SetEmptyStatus
{
    /**
     * Add "Empty string" in the order status (ref: B2BHW-1489)
     *
     * @param History $subject
     * @param array $result
     * @return array
     */
    public function afterGetStatuses(History $subject, array $result): array
    {
        if (!empty($result) && !array_key_exists('', $result)) {
            $emptyValArr = ['' => __('--- Please Select ---')];
            $result = $emptyValArr + $result;
        }
        return $result;
    }
}
