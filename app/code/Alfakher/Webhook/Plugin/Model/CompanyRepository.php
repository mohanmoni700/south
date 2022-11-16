<?php
namespace Alfakher\Webhook\Plugin\Model;

use Magento\Company\Api\CompanyRepositoryInterface as Subject;
use Magento\Company\Api\Data\CompanyInterface;

class CompanyRepository
{

    /**
     * Function for set extension attribute value
     *
     * @param Subject $subject
     * @param CompanyInterface $data
     * @return CompanyInterface
     */
    public function afterGet(
        Subject $subject,
        CompanyInterface $data
    ) {
        $extensionAttributes = $data->getExtensionAttributes();
        $extensionAttributes->setBusinessType($data->getBusinessType());
        $extensionAttributes->setAnnualTurnOver($data->getAnnualTurnOver());
        $extensionAttributes->setNumberOfEmp($data->getNumberOfEmp());
        $extensionAttributes->setTinNumber($data->getTinNumber());
        $extensionAttributes->setTobaccoPermitNumber($data->getTobaccoPermitNumber());
        $extensionAttributes->setHearAboutUs($data->getHearAboutUs());
        $extensionAttributes->setQuestions($data->getQuestions());
        $data->setExtensionAttributes($extensionAttributes);
        return $data;
    }

    /**
     * Function for save the custom attributes
     *
     * @param Subject $subject
     * @param CompanyInterface $company
     * @return CompanyInterface
     */
    public function beforeSave(
        Subject $subject,
        CompanyInterface $company
    ) {
        $extensionAttributes = $company->getExtensionAttributes() ?: $this->extensionFactory->create();
        if ($extensionAttributes !== null) {
            if ($extensionAttributes->getBusinessType() !== null) {
                $company->setBusinessType($extensionAttributes->getBusinessType());
            }
            if ($extensionAttributes->getAnnualTurnOver() !== null) {
                $company->setAnnualTurnOver($extensionAttributes->getAnnualTurnOver());
            }
            if ($extensionAttributes->getNumberOfEmp() !== null) {
                $company->setNumberOfEmp($extensionAttributes->getNumberOfEmp());
            }
            if ($extensionAttributes->getTinNumber() !== null) {
                $company->setTinNumber($extensionAttributes->getTinNumber());
            }
            if ($extensionAttributes->getTobaccoPermitNumber() !== null) {
                $company->setTobaccoPermitNumber($extensionAttributes->getTobaccoPermitNumber());
            }
            if ($extensionAttributes->getHearAboutUs() !== null) {
                $company->setHearAboutUs($extensionAttributes->getHearAboutUs());
            }
            if ($extensionAttributes->getQuestions() !== null) {
                $company->setQuestions($extensionAttributes->getQuestions());
            }
        }

        return [$company];
    }
}
