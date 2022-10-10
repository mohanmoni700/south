<?php
declare(strict_types=1);

namespace HookahShisha\InternationalTelephoneInput\Block;

use \HookahShisha\InternationalTelephoneInput\Helper\Data;
use \Magento\Directory\Api\CountryInformationAcquirerInterface;
use \Magento\Framework\Serialize\Serializer\Json;
use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;

class PhoneNumber extends Template
{

    /**
     * @var Json
     */
    protected $jsonHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformation;

    /**
     * PhoneNumber constructor.
     * @param Context $context
     * @param Json $jsonHelper
     * @param CountryInformationAcquirerInterface $countryInformation
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Json $jsonHelper,
        CountryInformationAcquirerInterface $countryInformation,
        Data $helper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->countryInformation = $countryInformation;
        parent::__construct($context);
    }

    /**
     * PhoneConfig
     *
     * @return bool|string
     */
    public function phoneConfig()
    {
        $config = [
            "nationalMode" => false,
            "separateDialCode" => true,
            "utilsScript" => $this->getViewFileUrl('HookahShisha_InternationalTelephoneInput::js/utils.js'),
            "preferredCountries" => [$this->helper->preferedCountry()],
        ];

        if ($this->helper->allowedCountries()) {
            $config["onlyCountries"] = explode(",", $this->helper->allowedCountries());
        }

        return $this->jsonHelper->serialize($config);
    }
}
