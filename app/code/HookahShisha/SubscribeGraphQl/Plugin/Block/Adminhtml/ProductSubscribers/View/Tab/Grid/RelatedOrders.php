<?php

namespace HookahShisha\SubscribeGraphQl\Plugin\Block\Adminhtml\ProductSubscribers\View\Tab\Grid;

use Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\Grid\RelatedOrders as Subject;
class RelatedOrders
{
    public function aroundAddColumn(Subject $subject, callable $proceed, $columnId, $column)
    {
        if ($columnId === 'base_grand_total') {
            return $subject;
        } else if ($columnId === 'grand_total') {
            $column['rate'] = 1;
        }
        return $proceed($columnId, $column);
    }
}
