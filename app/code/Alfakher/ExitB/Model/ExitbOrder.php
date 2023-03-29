<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model;

use Magento\Framework\Model\AbstractModel;
use Alfakher\ExitB\Model\ResourceModel\ExitbOrder as ExitbResourceModel;

class ExitbOrder extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(ExitbResourceModel::class);
    }
}
