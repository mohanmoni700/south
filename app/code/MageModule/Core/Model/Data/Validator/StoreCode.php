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
 * Class StoreCode
 *
 * @package MageModule\Core\Model\Data\Validator
 */
class StoreCode implements \MageModule\Core\Model\Data\ValidatorInterface
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
     * @var array
     */
    private $storeManager;

    /**
     * @var \MageModule\Core\Model\Data\Validator\Value
     */
    private $validator;

    /**
     * @var string[]
     */
    private $storeCodes;

    /**
     * StoreCode constructor.
     *
     * @param \MageModule\Core\Model\Data\Validator\ResultFactory $resultFactory
     * @param \MageModule\Core\Model\Data\Validator\ValueFactory  $validatorFactory
     * @param \Magento\Store\Model\StoreManagerInterface          $storeManager
     */
    public function __construct(
        \MageModule\Core\Model\Data\Validator\ResultFactory $resultFactory,
        \MageModule\Core\Model\Data\Validator\ValueFactory $validatorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultFactory    = $resultFactory;
        $this->validatorFactory = $validatorFactory;
        $this->storeManager     = $storeManager;
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
                $message = sprintf('The following store code is invalid: %s', current($invalidData));
            } elseif (count($invalidData) > 1) {
                $message = sprintf('The following store codes are invalid: %s', implode(', ', $invalidData));
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
    private function getStoreCodes()
    {
        if ($this->storeCodes === null) {
            $this->storeCodes = [];
            foreach ($this->storeManager->getStores(true) as $store) {
                $this->storeCodes[] = $store->getCode();
            }
        }

        return $this->storeCodes;
    }

    /**
     * @return \MageModule\Core\Model\Data\Validator\Value
     */
    private function getValidator()
    {
        if ($this->validator === null) {
            $this->validator = $this->validatorFactory->create(
                ['validValues' => $this->getStoreCodes()]
            );
        }

        return $this->validator;
    }
}
