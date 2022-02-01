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
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Helper\Directory;

use MageModule\Core\Helper\Data as CoreHelper;
use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Helper\Context;

/**
 * Class Region
 *
 * @package MageModule\Core\Helper\Directory
 */
class Region extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CoreHelper
     */
    private $helper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $idByNameOptions;

    /**
     * Region constructor.
     *
     * @param CoreHelper        $helper
     * @param Context           $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CoreHelper $helper,
        Context $context,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->helper            = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        if ($this->options === null) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();

            /** @var \Magento\Directory\Model\Region $region */
            foreach ($collection as $region) {
                $this->options[strtolower($region->getRegionId())] = $region->getName();
            }
        }

        return $this->options;
    }

    /**
     * Gets region name from it's numeric ID
     *
     * @param string $id
     *
     * @return string|null
     */
    public function getNameById($id)
    {
        $id      = strtolower($id);
        $options = $this->getOptions();

        return isset($options[$id]) ? $options[$id] : null;
    }

    /**
     * Gets array of $countryId => $regionId => $regionLabels
     *
     * @return array|null
     */
    private function getIdByNameOptions()
    {
        if ($this->idByNameOptions === null) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();

            /** @var \Magento\Directory\Model\Region $region */
            foreach ($collection as $region) {
                $countryId  = trim(strtolower($region->getCountryId()));
                $regionName = strtolower($this->helper->removeNonAlphaNumericChars($region->getName()));

                $this->idByNameOptions[$countryId][$region->getRegionId()] = $regionName;
            }
        }

        return $this->idByNameOptions;
    }

    /**
     * Get's region ID from the region name
     *
     * @param string $countryCode
     * @param string $regionName
     *
     * @return false|null|int
     */
    public function getIdByName($countryCode, $regionName)
    {
        $result      = null;
        $countryCode = trim(strtolower($countryCode));
        $regionName  = strtolower($this->helper->removeNonAlphaNumericChars($regionName));
        $options     = $this->getIdByNameOptions();

        if (isset($options[$countryCode])) {
            $result = array_search($regionName, $options[$countryCode]);
        }

        return $result;
    }
}
