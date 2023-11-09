<?php

declare(strict_types=1);

namespace Alfakher\SalesApprove\Plugin\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as Subject;

/**
 * Class Sales order collection
 */
class Collection
{
    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param $field
     * @param null $condition
     * @return mixed
     */
    public function aroundAddFieldToFilter(
        Subject  $subject,
        callable $proceed,
        $field,
        $condition = null
    ) {
        if ($field == 'guarantee' && $condition['eq'] == 'APPROVED') {
            $condition = ['in' => ['APPROVED', 'ACCEPT']];
        }
        return $proceed($field, $condition);
    }
}
