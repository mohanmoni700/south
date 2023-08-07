<?php

declare(strict_types=1);

namespace Corra\AmastyPromoGraphQl\Plugin\Model;

use Magento\SalesRule\Model\Utility;

class ExtendedUtility
{

    /**
     * @param Utility $subject
     * @param callable $proceed
     * @param $a1
     * @param $a2
     * @param bool $asString
     * @return array|string
     */
    public function aroundMergeIds(
        Utility $subject,
        callable $proceed,
        $a1,
        $a2,
        $asString = true
    ) {
        if (!is_array($a1)) {
            $a1 = empty($a1) ? [] : explode(',', $a1);
        }
        if (!is_array($a2)) {
            $a2 = empty($a2) ? [] : explode(',', $a2);
        }
        $a = array_unique(array_merge($a1, $a2));

        //To resolve applied_rule_ids comma issue in case of multiple coupons
        $a = array_filter($a, fn($value) => !is_null($value) && $value !== '');

        if ($asString) {
            $a = implode(',', $a);
        }
        return $a;
    }
}
