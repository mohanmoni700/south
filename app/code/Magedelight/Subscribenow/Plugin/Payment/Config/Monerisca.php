<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Plugin\Payment\Config;

use Magedelight\Subscribenow\Plugin\Payment\Validate;

class Monerisca
{

    /**
     * @var Validate
     */
    private $validate;

    /**
     * @param Validate $validate
     */
    public function __construct(Validate $validate)
    {
        $this->validate = $validate;
    }

    /**
     * @param $subject
     * @param $proceed
     * @return bool
     */
    public function aroundGetSaveCardOptional($subject, $proceed)
    {
        if ($this->validate->hasSubscriptionProduct()) {
            return false;
        }
        return $proceed();
    }
}
