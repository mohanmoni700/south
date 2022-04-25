<?php
declare (strict_types = 1);

namespace Alfakher\OutOfStockProduct\Plugin\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;

class CollectionUpdate
{
    /**
     * Skip Flags Params
     *
     * @var array
     */
    private $skipFlags = [];
    /**
     * [beforeSetOrder]
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param string $attribute
     * @param string $dir
     * @return [type]
     */
    public function beforeSetOrder(
        Collection $subject,
        $attribute,
        string $dir = Select::SQL_DESC
    ): array {
        $subject->setFlag('is_processing', true);
        $this->applyOutOfStockAtLastOrders($subject);

        $flagName = $this->_getFlag($attribute);

        if ($subject->getFlag($flagName)) {
            $this->skipFlags[] = $flagName;
        }

        $subject->setFlag('is_processing', false);
        return [$attribute, $dir];
    }
    /**
     * [_getFlag]
     *
     * @param  string $attribute
     * @return [type]
     */
    private function _getFlag(string $attribute): string
    {
        return 'sorted_by_' . $attribute;
    }
    /**
     * [aroundSetOrder]
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param string $proceed
     * @param string $attribute
     * @param string $dir
     *
     * @return [type]
     */
    public function aroundSetOrder(
        Collection $subject,
        callable $proceed,
        $attribute,
        string $dir = Select::SQL_DESC
    ): Collection {
        $flagName = $this->_getFlag($attribute);
        if (!in_array($flagName, $this->skipFlags)) {
            $proceed($attribute, $dir);
        }

        return $subject;
    }
    /**
     * [applyOutOfStockAtLastOrders ]
     *
     * @param  Collection $collection
     * @return [type]
     */
    private function applyOutOfStockAtLastOrders(Collection $collection)
    {
        if (!$collection->getFlag('is_sorted_by_oos')) {
            $collection->setFlag('is_sorted_by_oos', true);
            $collection->setOrder('out_of_stock_at_last', Select::SQL_DESC);
        }
    }
    /**
     * [beforeAddOrder]
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param string $attribute
     * @param string $dir
     *
     * @return [type]
     */
    public function beforeAddOrder(
        Collection $subject,
        $attribute,
        string $dir = Select::SQL_DESC
    ): array {
        if (!$subject->getFlag('is_processing')) {
            $result = $this->beforeSetOrder($subject, $attribute, $dir);
        }

        return $result ?? [$attribute, $dir];
    }
}
