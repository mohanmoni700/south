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
 * Class CountryId
 *
 * @package MageModule\Core\Model\Data\Validator
 */
class CountryId implements \MageModule\Core\Model\Data\ValidatorInterface
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
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \MageModule\Core\Model\Data\Validator\Value
     */
    private $validator;

    /**
     * @var string[]
     */
    private $countryIds;

    /**
     * StoreCode constructor.
     *
     * @param \MageModule\Core\Model\Data\Validator\ResultFactory              $resultFactory
     * @param \MageModule\Core\Model\Data\Validator\ValueFactory               $validatorFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory
     */
    public function __construct(
        \MageModule\Core\Model\Data\Validator\ResultFactory $resultFactory,
        \MageModule\Core\Model\Data\Validator\ValueFactory $validatorFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory
    ) {
        $this->resultFactory     = $resultFactory;
        $this->validatorFactory  = $validatorFactory;
        $this->collectionFactory = $collectionFactory;
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
                $message = sprintf('The following country ID is invalid: %s', current($invalidData));
            } elseif (count($invalidData) > 1) {
                $message = sprintf('The following country IDs are invalid: %s', implode(', ', $invalidData));
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
    private function getCountryIds()
    {
        if ($this->countryIds === null) {
            $this->countryIds = [];
            $collection = $this->collectionFactory->create();
            foreach ($collection as $object) {
                $this->countryIds[] = $object->getCountryId();
            }
        }

        return $this->countryIds;
    }

    /**
     * @return \MageModule\Core\Model\Data\Validator\Value
     */
    private function getValidator()
    {
        if ($this->validator === null) {
            $this->validator = $this->validatorFactory->create(
                ['validValues' => $this->getCountryIds()]
            );
        }

        return $this->validator;
    }
}
