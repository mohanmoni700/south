<?php

namespace Alfakher\Seamlesschex\Model\ResourceModel\SeamlesschexLog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init(
            \Alfakher\Seamlesschex\Model\SeamlesschexLog::class,
            \Alfakher\Seamlesschex\Model\ResourceModel\SeamlesschexLog::class
        );
    }
}
