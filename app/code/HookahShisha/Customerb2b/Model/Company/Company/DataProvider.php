<?php

namespace HookahShisha\Customerb2b\Model\Company\Company;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Company;

/**
 * Data provider for company.
 */
class DataProvider extends \Magento\Company\Model\Company\DataProvider
{

    /**
     * Get company general data.
     *
     * @param CompanyInterface $company
     * @return array
     */
    public function getGeneralData(CompanyInterface $company)
    {
        return [
            Company::NAME => $company->getCompanyName(),
            Company::STATUS => $company->getStatus(),
            Company::REJECT_REASON => $company->getRejectReason(),
            Company::REJECTED_AT => $company->getRejectedAt(),
            Company::COMPANY_EMAIL => $company->getCompanyEmail(),
            Company::SALES_REPRESENTATIVE_ID => $company->getSalesRepresentativeId(),
            'com_account_verified' => $company->getComAccountVerified(),
            'com_details_changed' => $company->getComDetailsChanged(),
            'com_verification_message' => $company->getComVerificationMessage(),
        ];
    }

    /**
     * Get company Business detail data.
     *
     * @param CompanyInterface $company
     * @return array
     */
    public function getBusinessdetailData(CompanyInterface $company)
    {
        return [
            'business_type' => $company->getBusinessType(),
            'annual_turn_over' => $company->getAnnualTurnOver(),
            'number_of_emp' => $company->getNumberOfEmp(),
            'tin_number' => $company->getTinNumber(),
            'fiscal_number' => $company->getFiscalNumber(),
            'ust_id' => $company->getUstId(),
            'tobacco_permit_number' => $company->getTobaccoPermitNumber(),
            'hear_about_us' => $company->getHearAboutUs(),
            'questions' => $company->getQuestions(),
        ];
    }

    /**
     * Get company result data.
     *
     * @param CompanyInterface $company
     * @return array
     */
    public function getCompanyResultData(CompanyInterface $company)
    {
        $result = [
            self::DATA_SCOPE_GENERAL => $this->getGeneralData($company),
            self::DATA_SCOPE_INFORMATION => $this->getInformationData($company),
            'businessdetails' => $this->getBusinessdetailData($company),
            self::DATA_SCOPE_ADDRESS => $this->getAddressData($company),
            self::DATA_SCOPE_COMPANY_ADMIN => $this->getCompanyAdminData($company),
            self::DATA_SCOPE_SETTINGS => $this->getSettingsData($company),
        ];
        $result['id'] = $company->getId();
        return $result;
    }
}
