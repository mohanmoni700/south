<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Adminhtml\Customer\Edit\Tab\Renderer;

class ProductName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Entity model name which must be used to retrieve entity specific data.
     * @var null|\Magento\Catalog\Model\ResourceModel\AbstractResource
     */
    protected $_entityResource;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product $entityResource,
        array $data = []
    ) {
        $this->_entityResource = $entityResource;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   Object $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $productId = $row->getProductId();
        if (!empty($productId)) { // MD here we get only product name without loading object
            return $this->_entityResource->getAttributeRawValue(
                $productId,
                'name',
                $row->getStoreId()
            );
        }
        return null;
    }
}
