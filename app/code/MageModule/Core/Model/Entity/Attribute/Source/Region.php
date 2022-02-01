<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright    2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Model\Entity\Attribute\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class Region extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var RegionCollectionFactory
     */
    private $regionFactory;

    /**
     * Region constructor.
     *
     * @param OptionCollectionFactory $attrOptionCollectionFactory
     * @param OptionFactory           $attrOptionFactory
     * @param RegionCollectionFactory $regionsFactory
     */
    public function __construct(
        OptionCollectionFactory $attrOptionCollectionFactory,
        OptionFactory $attrOptionFactory,
        RegionCollectionFactory $regionsFactory
    ) {
        $this->regionFactory = $regionsFactory;
        parent::__construct(
            $attrOptionCollectionFactory,
            $attrOptionFactory
        );
    }

    /**
     * @param bool $withEmpty Add empty option to array
     * @param bool $defaultValues
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->_createRegionsCollection()->load()->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @return RegionCollection
     */
    private function _createRegionsCollection()
    {
        return $this->regionFactory->create();
    }
}
