<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Model\Import;

use Magento\ImportExport\Model\Import;

class SerialCodeBasic extends \Magento\ImportExport\Model\Source\Import\AbstractBehavior
{
    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Add/Update')
        ];
    }
    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return 'serial_code_basic';
    }
}
