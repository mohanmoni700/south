<?php
/**
 * MultiQuickbooksConnect
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model\ResourceModel;

/**
 * MultiQuickbooksConnect account mysql resource.
 */
class CreditmemoMap extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('multi_quickbook_map_creditmemo', 'entity_id');
    }
}
