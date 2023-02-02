<?php
/**
 * @category   Webkul
 * @package    Webkul_QuickbookCustomInv
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\QuickbookCustomInv\Plugin\Helper\QuickBooks;

use QuickBooksOnline\API\DataService\DataService;

class Item
{
    /**
     * AroundIsAvailable.
     *
     * @param \Webkul\QuickbookCustomInv\Helper\QuickBooks\Item $subject
     * @param array $result
     * @param DataService $dataService
     * @param array $itemData
     * @return
     */
    public function afterGetItemFields(
        \Webkul\QuickbookCustomInv\Helper\QuickBooks\Item $subject,
        $result,
        DataService $dataService,
        array $itemData
    ) {
        $purchaseCost = $itemData['PurchaseCost'] ?? 0;
        if ($purchaseCost) {
            $result->PurchaseCost = $purchaseCost;
        }
        return $result;
    }
}
