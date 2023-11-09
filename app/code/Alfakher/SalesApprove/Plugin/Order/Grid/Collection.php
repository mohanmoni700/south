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
     * Filter field name
     */
    public const GUARANTEE = 'guarantee';

    /**
     * Filter condition value
     */
    public const APPROVED = 'APPROVED';

    /**
     * Filter condition value
     */
    public const ACCEPT = 'ACCEPT';

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
        if ($field == self::GUARANTEE && $condition['eq'] == self::APPROVED) {
            $condition = ['in' => [self::APPROVED, self::ACCEPT]];
        }
        return $proceed($field, $condition);
    }
}
