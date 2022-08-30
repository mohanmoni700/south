<?php

namespace Alfakher\Seamlesschex\Model\ResourceModel;

class SeamlesschexLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('seamlesschex_log', 'entity_id');
    }
}
