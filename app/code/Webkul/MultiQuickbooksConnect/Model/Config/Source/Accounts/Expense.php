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
namespace Webkul\MultiQuickbooksConnect\Model\Config\Source\Accounts;

use Magento\Framework\Data\OptionSourceInterface;

class Expense implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    private $jsonHelper;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\QuickBooks $quickBooks
     */
    private $quickBooks;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Logger\Logger $logger
     */
    private $logger;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper,
     * @param \Webkul\MultiQuickbooksConnect\Helper\QuickBooks $quickBooks,
     * @param \Webkul\MultiQuickbooksConnect\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MultiQuickbooksConnect\Helper\QuickBooks $quickBooks,
        \Webkul\MultiQuickbooksConnect\Logger\Logger $logger
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->quickBooks = $quickBooks;
        $this->logger = $logger;
    }

    public function toOptionArray()
    {
        $options = [];
        //
        return $options;
    }

    /**
     * Return options array
     *
     * @param int $accountId
     * @return array
     */
    public function getOptions($accountId)
    {
        try {
            $condition = "where AccountType='Cost of Goods Sold' and AccountSubType='SuppliesMaterialsCogs'";
            $accounts = $this->quickBooks->getAccounts($accountId, $condition);
            $expenseAccounts = [['value' => 'temp', 'label' => __('Get list after authenticate from Quickbooks')]];
            if ($accounts) {
                $expenseAccounts = [['value' => '', 'label' => __('Select Expense Account')]];
                $accounts = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($accounts));
                foreach ($accounts as $key => $account) {
                    $expenseAccounts[] = ['value' => $account['Id'], 'label' => $account['Name']];
                }
            }
            return $expenseAccounts;
        } catch (\Exception $e) {
            $this->logger->addError('Expense toOptionArray : '.$e->getMessage());
            return [['value' => '', 'label' => __('Get list after authenticate from Quickbooks')]];
        }
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray($accountId)
    {
        $optionList = $this->getOptions($accountId);
        $optionArray = [];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        // $optionArray = ['0' => 'test'];
        return $optionArray;
    }
}
