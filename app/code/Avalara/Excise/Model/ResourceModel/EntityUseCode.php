<?php

namespace Avalara\Excise\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class EntityUseCode extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('excise_entity_use_code', 'entity_use_code_id'); //excise_entity_use_code is the database table
    }
}
