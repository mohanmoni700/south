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

namespace MageModule\Core\Ui\Component\Listing\Column\Options;

class Yesno implements \Magento\Framework\Data\OptionSourceInterface
{
    const OPTION_VALUE_YES = '1';
    const OPTION_VALUE_NO = '0';

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options[self::OPTION_VALUE_YES]['label'] = 'Yes';
            $this->options[self::OPTION_VALUE_YES]['value'] = self::OPTION_VALUE_YES;

            $this->options[self::OPTION_VALUE_NO]['label'] = 'No';
            $this->options[self::OPTION_VALUE_NO]['value'] = self::OPTION_VALUE_NO;
        }

        return $this->options;
    }
}
