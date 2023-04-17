<?php

namespace Avalara\Excise\Model\ResourceModel\EntityUseCode;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Avalara\Excise\Model\EntityUseCode',
            'Avalara\Excise\Model\ResourceModel\EntityUseCode'
        );
    }
}