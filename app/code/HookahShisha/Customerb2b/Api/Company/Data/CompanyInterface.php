<?php

namespace HookahShisha\Customerb2b\Api\Company\Data;

use Magento\Company\Api\Data\CompanyInterface as CompanyData;

/**
 * Interface for Customerb2b Company entity.
 *
 */
interface CompanyInterface extends CompanyData
{

    public const BUSINESS_TYPE = 'business_type';
    public const ANNUAL_TURN_OVER = 'annual_turn_over';
    public const NUMBER_OF_EMP = 'number_of_emp';
    public const TIN_NUMBER = 'tin_number';
    public const TOBACCO_PERMIT_NUMBER = 'tobacco_permit_number';
    public const HEAR_ABOUT_US = 'hear_about_us';
    public const QUESTIONS = 'questions';
    public const COM_ACCOUNT_VERIFIED = 'com_account_verified';
    public const COM_DETAILS_CHANGED = 'com_details_changed';
    public const COM_VERIFICATION_MESSAGE = 'com_verification_message';
    public const UST_ID = 'ust_id';
    public const FISCAL_NUMBER = 'fiscal_number';

    /**
     * Set rejected at time.
     *
     * @param string $businessType
     * @return CompanyData
     */
    public function setBusinessType($businessType);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getBusinessType();

    /**
     * Set rejected at time.
     *
     * @param string $annualTurnOver
     * @return CompanyData
     */
    public function setAnnualTurnOver($annualTurnOver);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getAnnualTurnOver();

    /**
     * Set rejected at time.
     *
     * @param string $numberOfEmp
     * @return CompanyData
     */
    public function setNumberOfEmp($numberOfEmp);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getNumberOfEmp();

    /**
     * Set rejected at time.
     *
     * @param string $tinNumber
     * @return CompanyData
     */
    public function setTinNumber($tinNumber);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getTinNumber();

    /**
     * Set rejected at time.
     *
     * @param string $tobaccoPermitNumber
     * @return CompanyData
     */
    public function setTobaccoPermitNumber($tobaccoPermitNumber);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getTobaccoPermitNumber();

    /**
     * Set rejected at time.
     *
     * @param string $ustID
     * @return CompanyData
     */
    public function setUstId($ustID);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getUstId();

    /**
     * Set rejected at time.
     *
     * @param string $fiscalNumber
     * @return CompanyData
     */
    public function setFiscalNumber($fiscalNumber);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getFiscalNumber();

    /**
     * Set rejected at time.
     *
     * @param string $hearAboutUs
     * @return CompanyData
     */
    public function setHearAboutUs($hearAboutUs);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getHearAboutUs();

    /**
     * Set rejected at time.
     *
     * @param string $questions
     * @return CompanyData
     */
    public function setQuestions($questions);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getQuestions();

    /**
     * Set rejected at time.
     *
     * @param int $comAccountVerified
     * @return CompanyData
     */
    public function setComAccountVerified($comAccountVerified);

    /**
     * Get rejected at time.
     *
     * @return int
     */
    public function getComAccountVerified();

    /**
     * Set rejected at time.
     *
     * @param string $comVerificationMessage
     * @return CompanyData
     */
    public function setComVerificationMessage($comVerificationMessage);

    /**
     * Get rejected at time.
     *
     * @return string
     */
    public function getComVerificationMessage();

    /**
     * Set rejected at time.
     *
     * @param int $comDetailsChanged
     * @return CompanyData
     */
    public function setComDetailsChanged($comDetailsChanged);

    /**
     * Get rejected at time.
     *
     * @return int
     */
    public function getComDetailsChanged();
}
