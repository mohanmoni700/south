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
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Country extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var CountryCollectionFactory
     */
    private $countryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Country constructor.
     *
     * @param OptionCollectionFactory  $attrOptionCollectionFactory
     * @param OptionFactory            $attrOptionFactory
     * @param CountryCollectionFactory $countriesFactory
     * @param StoreManagerInterface    $storeManager
     */
    public function __construct(
        OptionCollectionFactory $attrOptionCollectionFactory,
        OptionFactory $attrOptionFactory,
        CountryCollectionFactory $countriesFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->countryFactory = $countriesFactory;
        $this->storeManager   = $storeManager;
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
     * @throws NoSuchEntityException
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->createCountriesCollection()->loadByStore(
                $this->storeManager->getStore()->getId()
            )->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * @param int|string $value
     *
     * @return string|string[]
     * @throws NoSuchEntityException
     */
    public function getOptionText($value)
    {
        $result    = [];
        $origValue = $value;
        $values    = explode(',', $value);

        $options = $this->getAllOptions();

        foreach ($values as $value) {
            foreach ($options as $option) {
                if (isset($option['value']) && $option['value'] === $value) {
                    $result[$value] = $option['label'];
                }
            }
        }

        if (empty($result)) {
            $result = $origValue;
        } elseif (count($result) === 1) {
            $result = current($result);
        }

        return $result;
    }

    /**
     * @return CountryCollection
     */
    private function createCountriesCollection()
    {
        return $this->countryFactory->create();
    }
}
