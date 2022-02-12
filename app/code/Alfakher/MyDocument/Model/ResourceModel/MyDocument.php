<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Alfakher\MyDocument\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MyDocument extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('alfakher_mydocument_mydocument', 'mydocument_id');
    }
}
