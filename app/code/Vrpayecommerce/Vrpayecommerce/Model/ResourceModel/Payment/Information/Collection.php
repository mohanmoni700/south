<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\ResourceModel\Payment\Information;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
        	'Vrpayecommerce\Vrpayecommerce\Model\Payment\Information',
        	'Vrpayecommerce\Vrpayecommerce\Model\ResourceModel\Payment\Information'
        );
    }
}
