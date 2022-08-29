<?php
namespace Alfakher\Webhook\Plugin\Model;

use Magento\Company\Api\CompanyRepositoryInterface as Subject;

class CompanyRepository
{
    /**
     * @var companyRepository
     */
    protected $companyRepository;
    /**
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __constructor(\Magento\Company\Api\CompanyRepositoryInterface $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    /**
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
