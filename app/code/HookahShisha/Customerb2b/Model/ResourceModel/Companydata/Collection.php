<?php
declare (strict_types = 1);

namespace HookahShisha\Customerb2b\Model\ResourceModel\Companydata;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var _idFieldName
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \HookahShisha\Customerb2b\Model\Company\Company::class,
            \HookahShisha\Customerb2b\Model\ResourceModel\Companydata::class
        );
    }
}
