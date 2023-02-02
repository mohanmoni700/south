<?php

namespace Magedelight\Subscribenow\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection;

class CustomerGroup implements ArrayInterface
{

    /**
     * @var Collection
     */
    private $collection;

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $customerGroups = $this->collection->toOptionArray();
        array_shift($customerGroups);
        return $customerGroups;
    }
}
