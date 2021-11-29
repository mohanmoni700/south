<?php
/**
 * @author  CORRA
 */
declare(strict_types=1);

namespace HookahShisha\Veratad\Model\Config\Source\Group;

use \Magento\Customer\Model\ResourceModel\Group\Collection;

class Multiselect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Collection
     */
    protected $_customerGroup;

    /**
     * @var array
     */
    protected $_options;

    /**
     * Multiselect constructor.
     * @param Collection $customerGroup
     */
    public function __construct(Collection $customerGroup)
    {
        $this->_customerGroup = $customerGroup;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->_customerGroup->toOptionArray();
        }
        return $this->_options;
    }
}
