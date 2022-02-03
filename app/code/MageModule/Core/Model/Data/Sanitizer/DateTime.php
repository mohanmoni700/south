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

namespace MageModule\Core\Model\Data\Sanitizer;

/**
 * Class DateTime
 *
 * @package MageModule\Core\Model\Data\Sanitizer
 */
class DateTime implements \MageModule\Core\Model\Data\SanitizerInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * Date constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitize($value)
    {
        try {
            $value = str_replace('-', '/', $value);
            $value = preg_replace("/[^A-Za-z0-9:\/ ]/", "", trim($value));
            if ($value) {
                $value = $this->dateTime->formatDate($value, true);
            } else {
                $value = null;
            }
        } catch (\Exception $e) {
            $value = null;
        }

        return $value;
    }
}
