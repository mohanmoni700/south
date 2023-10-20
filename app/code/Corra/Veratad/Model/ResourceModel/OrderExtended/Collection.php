<?php

namespace Corra\Veratad\Model\ResourceModel\OrderExtended;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Corra\Veratad\Model\OrderExtended;

/**
 * OrderExtended Collection
 */
class Collection extends AbstractCollection
{
    /** @var string  */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(
            OrderExtended::class,
            \Corra\Veratad\Model\ResourceModel\OrderExtended::class
        );
    }
}
