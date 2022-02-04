<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Alfakher\MyDocument\Model\ResourceModel\MyDocument;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'mydocument_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Alfakher\MyDocument\Model\MyDocument::class,
            \Alfakher\MyDocument\Model\ResourceModel\MyDocument::class
        );
    }
}

