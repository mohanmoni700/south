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

namespace MageModule\Core\Exception;

use MageModule\Core\Model\Data\Validator\ResultInterface;

/**
 * Class ValidatorException
 *
 * @package MageModule\Core\Exception
 */
class ValidatorException extends \Exception
{
    /**
     * ValidatorException constructor.
     *
     * @param ResultInterface|ResultInterface[] $validators
     * @param int                               $code
     * @param \Exception|null                   $previous
     */
    public function __construct($validators = [], $code = 0, \Exception $previous = null)
    {
        if (is_array($validators)) {
            $messages = [];
            foreach ($validators as $validator) {
                $messages[] = $validator->getMessage();
            }

            $messages = array_filter($messages, 'strlen');
            $message  = implode(PHP_EOL, $messages);
        } else {
            $message = $validators->getMessage();
        }

        parent::__construct($message, $code, $previous);
    }
}
