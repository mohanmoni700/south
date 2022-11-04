<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderToken extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('exitb_token', 'entity_id');
    }
}
