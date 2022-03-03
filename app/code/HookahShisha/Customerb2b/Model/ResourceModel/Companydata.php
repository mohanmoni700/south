<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace HookahShisha\Customerb2b\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Companydata extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('company', 'entity_id');
    }
}
