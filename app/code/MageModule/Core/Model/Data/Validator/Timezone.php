<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\Data\Validator;

/**
 * Class Timezone
 *
 * @package MageModule\Core\Model\Data\Validator
 */
class Timezone implements \MageModule\Core\Model\Data\ValidatorInterface
{
    /**
     * @var \MageModule\Core\Model\Data\Validator\ResultFactory
     */
    private $resultFactory;

    /**
     * @var \MageModule\Core\Model\Data\Validator\ValueFactory
     */
    private $validatorFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Timezone
     */
    private $source;

    /**
     * @var array
     */
    private $timezones;

    /**
     * @var \MageModule\Core\Model\Data\Validator\Value
     */
    private $validator;

    /**
     * Timezone constructor.
     *
     * @param \MageModule\Core\Model\Data\Validator\ResultFactory $resultFactory
     * @param \MageModule\Core\Model\Data\Validator\ValueFactory  $validatorFactory
     * @param \Magento\Config\Model\Config\Source\Locale\Timezone $source
     */
    public function __construct(
        \MageModule\Core\Model\Data\Validator\ResultFactory $resultFactory,
        \MageModule\Core\Model\Data\Validator\ValueFactory $validatorFactory,
        \Magento\Config\Model\Config\Source\Locale\Timezone $source
    ) {
        $this->resultFactory    = $resultFactory;
        $this->validatorFactory = $validatorFactory;
        $this->source           = $source;
    }

    /**
     * @param array $data
     *
     * @return \MageModule\Core\Model\Data\Validator\ResultInterface
     */
    public function validate(array $data)
    {
        $result = $this->getValidator()->validate($data);

        if (!$result->isValid()) {
            $invalidData = $result->getInvalidData();
            $message     = null;
            if (count($invalidData) === 1) {
                $message = sprintf('The following timezone is invalid: %s', current($invalidData));
            } elseif (count($invalidData) > 1) {
                $message = sprintf('The following timezones are invalid: %s', implode(', ', $invalidData));
            }

            $result = $this->resultFactory->create(
                [
                    'isValid'     => empty($invalidData),
                    'message'     => $message,
                    'invalidData' => $invalidData
                ]
            );
        }

        return $result;
    }

    /**
     * @return string[]
     */
    private function getTimezones()
    {
        if ($this->timezones === null) {
            $this->timezones = [];

            foreach ($this->source->toOptionArray() as $item) {
                if ($item['value']) {
                    $this->timezones[] = $item['value'];
                }
            }
        }

        return $this->timezones;
    }

    /**
     * @return \MageModule\Core\Model\Data\Validator\Value
     */
    private function getValidator()
    {
        if ($this->validator === null) {
            $this->validator = $this->validatorFactory->create(
                ['validValues' => $this->getTimezones()]
            );
        }

        return $this->validator;
    }
}
