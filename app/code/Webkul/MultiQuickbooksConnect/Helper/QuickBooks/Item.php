<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Helper\QuickBooks;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPItem;
use Webkul\MultiQuickbooksConnect\Logger\Logger;
use Webkul\MultiQuickbooksConnect\Helper\Data as HelperData;

/**
 * MultiQuickbooksConnect Item helper
 */
class Item extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\Data $helperData
     */
    private $helperData;

    /**
     * @var Webkul\MultiQuickbooksConnect\Logger\Logger $logger
     */
    private $logger;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        HelperData $helperData,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * @param DataService $dataService
     * @param array $itemData
     * @return IPPItem
     */
    public function getItemFields(DataService $dataService, $itemData, $accountId)
    {
        $accountConfig = $this->helperData->getQuickbooksAccountConfig($accountId);
        $item = new IPPItem();
        $item->Name = $itemData['Name'];
        $item->Active = 'true';
        $item->Taxable = $itemData['isTaxablePro'] ? 'true' : 'false';
        $item->UnitPrice = isset($itemData['MainPrice']) ? $itemData['MainPrice'] : $itemData['UnitPrice'];
        $item->Type = $itemData['Type'];
        $item->TrackQtyOnHand = $itemData['TrackQtyOnHand'];
        if ($itemData['Type'] == 'Inventory') {
            $item->QtyOnHand = $itemData['QtyOnHand'];
            $item->InvStartDate = $itemData['InvStartDate'];
            $item->AssetAccountRef = $accountConfig['asset_account'];
            $item->Description = $itemData['Description'] ? $itemData['Description'] : $itemData['Name'];
        }
        $item->Sku = $itemData['Sku'];
        $item->IncomeAccountRef = $accountConfig['income_account'];
        $item->ExpenseAccountRef = $accountConfig['expense_account'];
        return $item;
    }

    /**
     * @param DataService $dataService
     * @param array $itemData
     * @return IPPItem
     */
    public function createItem(DataService $dataService, $itemData, $accountId)
    {
        return $dataService->Add($this->getItemFields($dataService, $itemData, $accountId));
    }

    /**
     * @param DataService $dataService
     * @param array $itemData
     * @return IPPItem
     */
    public function getItem(DataService $dataService, $itemData, $accountId)
    {
        // @codingStandardsIgnoreStart
        $query = "SELECT * FROM Item Where Sku like '".$itemData['Sku']."%'";
        $item = $dataService->Query($query);
        if ($item == null && !preg_match('/[&+=\[\]\';,.\/{}|"<>?~\\\\]/', $itemData['Name'])) {
            $query = "SELECT * FROM Item Where Name like '".$itemData['Name']."%'";
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
