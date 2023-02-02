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

namespace Magedelight\Subscribenow\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class IntervalType extends Value
{

    const INTERVAL_TYPE_DAY = 0;
    const INTERVAL_TYPE_WEEK = 1;
    const INTERVAL_TYPE_MONTH = 2;
    const INTERVAL_TYPE_YEAR = 3;

    const INTERVAL = [
        'day' => 1,
        'week' => 2,
        'month' => 3,
        'year' => 4,
    ];

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var Json
     */
    protected $serialize;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Random $mathRandom
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param Json $serialize
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Random $mathRandom,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        Json $serialize,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        $this->serialize = $serialize;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save.
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $result = [];
        foreach ($value as $data) {
            if (!$data) {
                continue;
            }
            if (!is_array($data)) {
                continue;
            }
            if (count($data) < 2) {
                continue;
            }
            $country = $data['interval_type'];
            if (array_key_exists($country, $result)) {
                $result[$country] = array_unique(array_merge($result[$country], $data['no_of_interval']));
            } else {
                $result[$country] = $data['no_of_interval'];
                $result[$country] = $data['interval_label'];
            }
        }
        $this->setValue($this->serializer->serialize($value));

        return $this;
    }

    /**
     * Process data after load.
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->serialize->unserialize($value);
        if (is_array($value)) {
            $value = $this->encodeArrayFieldValue($value);
            $this->setValue($value);
        }

        return $this;
    }

    /**
     * Encode value to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray.
     *
     * @param array $value
     *
     * @return array
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = [];
        $intevalCount = count($value);
        foreach ($value as $uniqueId => $intervalValue) {
            if (!empty($intervalValue)) {
                $resultId = $this->mathRandom->getUniqueHash('_');
                $result[$resultId] = ['interval_type' => $intervalValue['interval_type'], 'no_of_interval' => $intervalValue['no_of_interval'], 'interval_label' => $intervalValue['interval_label']];
            }
        }

        return $result;
    }
}
