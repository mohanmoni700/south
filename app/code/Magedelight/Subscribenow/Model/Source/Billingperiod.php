<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package  Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Billingperiod extends \Magento\Framework\Model\AbstractModel implements ArrayInterface
{

    const INTERVAL_LABEL = 'md_subscribenow/general/manage_subscription_interval';

    /**
     * @var scopeconfig
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serialize;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serialize = $serialize;
    }

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $subscriptionIntervals = $this->serialize->unserialize($this->scopeConfig->getValue(self::INTERVAL_LABEL, $storeScope));
        $intervalLabels = [];
        if (!empty($subscriptionIntervals)) {
            foreach ($subscriptionIntervals as $key => $value) {
                $intervalLabels[] = ['value' => $key, 'label' => $value['interval_label']];
            }
        }

        return $intervalLabels;
    }

    /**
     * get options as key value pair.
     *
     * @return array
     */
    public function getOptions()
    {
        $_tmpOptions = $this->toOptionArray();
        $_options = [];
        $_options[''] = '--Please select an option--';
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }

        return $_options;
    }
}
