<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model\ResourceModel\ExitbOrder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Alfakher\ExitB\Model\ExitbOrder as ExitbModel;
use Alfakher\ExitB\Model\ResourceModel\ExitbOrder as ExitbResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ExitbModel::class,
            ExitbResourceModel::class
        );
    }
}
