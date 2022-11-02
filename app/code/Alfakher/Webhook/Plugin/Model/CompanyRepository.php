<?php
namespace Alfakher\Webhook\Plugin\Model;

use Magento\Company\Api\CompanyRepositoryInterface as Subject;

class CompanyRepository
{

    /**
     * Function for set extension attribute value
     *
     * @param Subject $subject
     * @param CategoryInterface $data
     * @return CategoryInterface
     */
    public function afterGet(
        Subject $subject,
        \Magento\Company\Api\Data\CompanyInterface $data
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
}
