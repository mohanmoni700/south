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
namespace Webkul\QuickbookCustomInv\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Location implements OptionSourceInterface
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
     * Construct
     *
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\MultiQuickbooksConnect\Helper\QuickBooks $quickBooks
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

    /**
     * ToOptionArray
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
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
            $taxCodes = $this->quickBooks->getDepartmentList($accountId);
            $taxCodeList = [['value' => 'temp', 'label' => __('Get list after authenticate from Quickbooks')]];
            if ($taxCodes) {
                $taxCodeList = [['value' => '', 'label' => __('Select Business')]];
                $taxCodes = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($taxCodes));
                foreach ($taxCodes as $key => $taxCode) {
                    $taxCodeList[] = ['value' => $taxCode['Id'], 'label' => $taxCode['Name']];
                }
            }
            return $taxCodeList;
        } catch (\Exception $e) {
            $this->logger->addError('Business toOptionArray : '.$e->getMessage());
            return [['value' => '', 'label' => __('Get list after authenticate from Quickbooks')]];
        }
    }

    /**
     * Get options in "key-value" format
     *
     * @param int $accountId
     * @return array
     */
    public function toArray($accountId)
    {
        $optionList = $this->getOptions($accountId);
        $optionArray = [];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}
