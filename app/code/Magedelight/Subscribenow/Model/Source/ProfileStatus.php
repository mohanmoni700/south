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

class ProfileStatus implements ArrayInterface
{

    const PENDING_STATUS = 0;
    const ACTIVE_STATUS = 1;
    const PAUSE_STATUS = 2;
    const EXPIRED_STATUS = 3;
    const CANCELED_STATUS = 4;
    const SUSPENDED_STATUS = 5;
    const FAILED_STATUS = 6;
    const COMPLETED_STATUS = 7;
    const RENEWED_STATUS = 8;

    /**
     * @var Magedelight\Subscribenow\Helper\Data
     */
    private $helper;

    /**
     * @param \Magedelight\Subscribenow\Helper\Data $helper
     */
    public function __construct(
        \Magedelight\Subscribenow\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $status = $this->helper->getStatusLabel();
        $statusarray = [];
        foreach ($status as $key => $value) {
            $statusarray[] = [
                'value' => $key,
                'label' => $value,
            ];
        }
        unset($statusarray[0]); // Remove Unknown label from filter

        return $statusarray;
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
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }

        return $_options;
    }
}
