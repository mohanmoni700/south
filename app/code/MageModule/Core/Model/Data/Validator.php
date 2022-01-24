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

namespace MageModule\Core\Model\Data;

/**
 * Class Validator
 *
 * @package MageModule\Core\Model\Data
 */
class Validator
{
    /**
     * @var \MageModule\Core\Model\Data\ValidatorInterface[]
     */
    private $pool;

    /**
     * Validator constructor.
     *
     * @param \MageModule\Core\Model\Data\ValidatorInterface[] $pool
     */
    public function __construct($pool = [])
    {
        $this->pool = $pool;
    }

    /**
     * Returns true if valid, otherwise returns array containing result objects
     *
     * @param array $data
     *
     * @return bool|\MageModule\Core\Model\Data\Validator\ResultInterface[]
     */
    public function validate(array $data)
    {
        $result = [];

        foreach ($this->pool as $validator) {
            $validatorResult = $validator->validate($data);
            if (!$validatorResult->isValid()) {
                $result[] = $validatorResult;
            }
        }

        return empty($result) ? true : $result;
    }
}
