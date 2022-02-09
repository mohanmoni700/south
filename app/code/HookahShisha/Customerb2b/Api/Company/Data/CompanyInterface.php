<?php

namespace HookahShisha\Customerb2b\Api\Company\Data;

/**
 * Interface for Company entity.
 *
 * @api
 * @since 100.0.0
 */
interface CompanyInterface extends \Magento\Company\Api\Data\CompanyInterface
{

    const BUSINESS_TYPE = 'business_type';
    const ANNUAL_TURN_OVER = 'annual_turn_over';
    const NUMBER_OF_EMP = 'number_of_emp';
    const TIN_NUMBER = 'tin_number';
    const TOBACCO_PERMIT_NUMBER = 'tobacco_permit_number';
    const HEAR_ABOUT_US = 'hear_about_us';
    const QUESTIONS = 'questions';
    const COM_ACCOUNT_VERIFIED = 'com_account_verified';
    const COM_DETAILS_CHANGED = 'com_details_changed';
    const COM_VERIFICATION_MESSAGE = 'com_verification_message';

    /**
     * Set rejected at time.
     *
     * @param string $businessType
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @param string $hearAboutUs
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
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
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function setComDetailsChanged($comDetailsChanged);

    /**
     * Get rejected at time.
     *
     * @return int
     */
    public function getComDetailsChanged();
}
