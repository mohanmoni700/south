<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace HookahShisha\Customerb2b\Api\Company\Data;

/**
 * Interface for Company entity.
 *
 * @api
 * @since 100.0.0
 */
interface CompanyInterface extends \Magento\Company\Api\Data\CompanyInterface
{
    /*const COMPANY_ID = 'entity_id';
    const STATUS = 'status';
    const NAME = 'company_name';
    const LEGAL_NAME = 'legal_name';
    const COMPANY_EMAIL = 'company_email';
    const EMAIL = 'email';
    const VAT_TAX_ID = 'vat_tax_id';
    const RESELLER_ID = 'reseller_id';
    const COMMENT = 'comment';
    const STREET = 'street';
    const CITY = 'city';
    const COUNTRY_ID = 'country_id';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    const POSTCODE = 'postcode';
    const TELEPHONE = 'telephone';
    const JOB_TITLE = 'job_title';
    const PREFIX = 'prefix';
    const FIRSTNAME = 'firstname';
    const MIDDLENAME = 'middlename';
    const LASTNAME = 'lastname';
    const SUFFIX = 'suffix';
    const GENDER = 'gender';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const SALES_REPRESENTATIVE_ID = 'sales_representative_id';
    const REJECT_REASON = 'reject_reason';
    const REJECTED_AT = 'rejected_at';
    const SUPER_USER_ID = 'super_user_id';

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_BLOCKED = 3;*/

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
