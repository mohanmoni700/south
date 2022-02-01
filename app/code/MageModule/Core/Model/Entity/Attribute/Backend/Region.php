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

namespace MageModule\Core\Model\Entity\Attribute\Backend;

use Magento\Directory\Model\Region as RegionModel;
use Magento\Directory\Model\RegionFactory;

/**
 * Class Region
 *
 * @package MageModule\Core\Model\Entity\Attribute\Backend\Address
 */
class Region extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * Region constructor.
     *
     * @param RegionFactory $regionFactory
     */
    public function __construct(RegionFactory $regionFactory)
    {
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this|\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    public function beforeSave($object)
    {
        $region = $object->getData('region_id');
        if (is_numeric($region)) {
            /** @var RegionModel $regionModel */
            $regionModel = $this->regionFactory->create();
            $regionModel->load($region);
            if ($regionModel->getId() && $object->getCountryId() == $regionModel->getCountryId()) {
                $object->setRegionId($regionModel->getId());
                $object->setRegion($regionModel->getName());
            }
        }

        return $this;
    }
}
