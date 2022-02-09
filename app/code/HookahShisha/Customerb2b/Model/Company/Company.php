<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace HookahShisha\Customerb2b\Model\Company;

use HookahShisha\Customerb2b\Api\Company\Data\CompanyInterface;

/**
 * Class that implements interface for data transfer object of company entity.
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Company extends \Magento\Company\Model\Company implements CompanyInterface
{

    /**
     * Set rejected at time.
     *
     * @param string $businessType
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setBusinessType($businessType)
    {
        return $this->setData(self::BUSINESS_TYPE, $businessType);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getBusinessType()
    {
        return $this->getData(self::BUSINESS_TYPE);
    }

    /**
     * Set rejected at time.
     *
     * @param string $annualTurnOver
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setAnnualTurnOver($annualTurnOver)
    {
        return $this->setData(self::ANNUAL_TURN_OVER, $annualTurnOver);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getAnnualTurnOver()
    {
        return $this->getData(self::ANNUAL_TURN_OVER);
    }

    /**
     * Set rejected at time.
     *
     * @param string $numberOfEmp
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setNumberOfEmp($numberOfEmp)
    {
        return $this->setData(self::NUMBER_OF_EMP, $numberOfEmp);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getNumberOfEmp()
    {
        return $this->getData(self::NUMBER_OF_EMP);
    }

    /**
     * Set rejected at time.
     *
     * @param string $tinNumber
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setTinNumber($tinNumber)
    {
        return $this->setData(self::TIN_NUMBER, $tinNumber);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getTinNumber()
    {
        return $this->getData(self::TIN_NUMBER);
    }

    /**
     * Set rejected at time.
     *
     * @param string $tobaccoPermitNumber
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setTobaccoPermitNumber($tobaccoPermitNumber)
    {
        return $this->setData(self::TOBACCO_PERMIT_NUMBER, $tobaccoPermitNumber);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getTobaccoPermitNumber()
    {
        return $this->getData(self::TOBACCO_PERMIT_NUMBER);
    }

    /**
     * Set rejected at time.
     *
     * @param string $hearAboutUs
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setHearAboutUs($hearAboutUs)
    {
        return $this->setData(self::HEAR_ABOUT_US, $hearAboutUs);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getHearAboutUs()
    {
        return $this->getData(self::HEAR_ABOUT_US);
    }

    /**
     * Set rejected at time.
     *
     * @param string $questions
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setQuestions($questions)
    {
        return $this->setData(self::QUESTIONS, $questions);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getQuestions()
    {
        return $this->getData(self::QUESTIONS);
    }

    /**
     * Set rejected at time.
     *
     * @param int $comAccountVerified
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setComAccountVerified($comAccountVerified)
    {
        return $this->setData(self::COM_ACCOUNT_VERIFIED, $comAccountVerified);
    }

    /**
     * Get rejected at time.
     *
     * @return int
     */
    public function getComAccountVerified()
    {
        return $this->getData(self::COM_ACCOUNT_VERIFIED);
    }

    /**
     * Set rejected at time.
     *
     * @param int $comDetailsChanged
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setComDetailsChanged($comDetailsChanged)
    {
        return $this->setData(self::COM_DETAILS_CHANGED, $comDetailsChanged);
    }

    /**
     * Get rejected at time.
     *
     * @return int
     */
    public function getComDetailsChanged()
    {
        return $this->getData(self::COM_DETAILS_CHANGED);
    }

    /**
     * Set rejected at time.
     *
     * @param string $comVerificationMessage
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setComVerificationMessage($comVerificationMessage)
    {
        return $this->setData(self::COM_VERIFICATION_MESSAGE, $comVerificationMessage);
    }

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getComVerificationMessage()
    {
        return $this->getData(self::COM_VERIFICATION_MESSAGE);
    }
}
