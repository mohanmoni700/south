<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_QuickbookCustomInv
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\QuickbookCustomInv\Helper\QuickBooks;

use QuickBooksOnline\API\DataService\DataService;

/**
 * QuickbookCustomInv Item helper
 */
class Item extends \Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Item
{
    /**
     * @param DataService $dataService
     * @param array $itemData
     * @return IPPItem
     */
    public function getItem(DataService $dataService, $itemData, $accountId)
    {
        // @codingStandardsIgnoreStart
        $query = "SELECT * FROM Item Where Sku like '".addslashes($itemData['Sku'])."%'";
        $item = $dataService->Query($query);
        if ($item == null && !preg_match('/[&+=\[\]\';,.\/{}|"<>?~\\\\]/', $itemData['Name'])) {
            $query = "SELECT * FROM Item Where Name like '".addslashes($itemData['Name'])."%'";
            $item = $dataService->Query($query);
        }
        // @codingStandardsIgnoreEnd
        if ($item == null) {
            return $this->createItem($dataService, $itemData, $accountId);
        } else {
            return isset($item[0]) ? $item[0] : $item;
        }
    }
}
