<?php

namespace Avalara\Excise\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    const LEVEL_FIELD_NAME = 'level';
    const CREATED_AT_FIELD_NAME = 'created_at';

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('excise_log', 'log_id');
    }
}
