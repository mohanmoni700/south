<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model\ResourceModel\OrderToken;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Alfakher\ExitB\Model\OrderToken as TokenModel;
use Alfakher\ExitB\Model\ResourceModel\OrderToken as TokenResourceModel;

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
            TokenModel::class,
            TokenResourceModel::class
        );
    }
}
