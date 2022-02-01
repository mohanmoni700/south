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

namespace MageModule\Core\Model\Data\Validator;

/**
 * Validates that value exists in a pre-defined set of possible values
 *
 * Class Value
 *
 * @package MageModule\Core\Model\Data\Validator
 */
class Value implements \MageModule\Core\Model\Data\ValidatorInterface
{
    /**
     * @var \MageModule\Core\Model\Data\Validator\ResultFactory
     */
    private $resultFactory;

    /**
     * @var array
     */
    private $validValues;

    /**
     * Value constructor.
     *
     * @param \MageModule\Core\Model\Data\Validator\ResultFactory $resultFactory
     * @param array                                               $validValues
     */
    public function __construct(
        \MageModule\Core\Model\Data\Validator\ResultFactory $resultFactory,
        array $validValues
    ) {
        $this->resultFactory = $resultFactory;
        $this->validValues   = $validValues;
    }

    /**
     * @param array $data
     *
     * @return ResultInterface
     */
    public function validate(array $data)
    {
        $message       = null;
        $invalidValues = [];
        foreach ($data as $key => $value) {
            if (!is_object($value) && !is_array($value) && !in_array($value, $this->validValues)) {
                $invalidValues[] = $value;
            }
        }

        if (count($invalidValues) === 1) {
            $message = sprintf('The following value is not valid: %s', current($invalidValues));
        } elseif (count($invalidValues) > 1) {
            $message = sprintf('The following values are not valid: %s', implode(', ', $invalidValues));
        }

        $result = $this->resultFactory->create(
            [
                'isValid'     => empty($invalidValues),
                'message'     => $message,
                'invalidData' => $invalidValues
            ]
        );

        return $result;
    }
}
