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
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\OrderMap\Grid\Tab;
 
use Magento\Framework\DataObject;
use Webkul\MultiQuickbooksConnect\Model\Source\Store as StoreText;
 
class Store extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var StoreText
     */
    private $storeText;

    /**
     * @param StoreText $storeText
     */
    public function __construct(
        StoreText $storeText
    ) {
        $this->storeText = $storeText;
    }

    /**
     * Get store label
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $statusVal = $row->getData('store_id');
        $statusLabel = $this->storeText->getOptionText($statusVal);
        return $statusLabel;
    }
}
