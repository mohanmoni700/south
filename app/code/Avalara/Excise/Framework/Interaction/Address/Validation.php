<?php

namespace Avalara\Excise\Framework\Interaction\Address;

use Avalara\Excise\Exception\AddressValidateException;
use Avalara\Excise\Exception\AvalaraConnectionException;
use Avalara\Excise\Framework\Interaction\Address;
use Avalara\Excise\Api\RestAddressInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\DataObjectFactory;
use Avalara\Excise\Helper\Rest\Config as RestConfig;

class Validation
{
    /**
     * @var Address
     */
    protected $interactionAddress;

    /**
     * @var RestAddressInterface
     */
    protected $addressService;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var RestConfig
     */
    protected $restConfig;

    /**
     * @param Address $interactionAddress
     * @param RestAddressInterface $addressService
     * @param DataObjectFactory $dataObjectFactory
     * @param RestConfig $restConfig
     */
    public function __construct(
        Address $interactionAddress,
        RestAddressInterface $addressService,
        DataObjectFactory $dataObjectFactory,
        RestConfig $restConfig
    ) {
        $this->interactionAddress = $interactionAddress;
        $this->addressService = $addressService;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->restConfig = $restConfig;
    }

    /**
     * Validate address using AvaTax Address Validation API
     *
     * @param array|\Magento\Customer\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|/AvaTax/ValidAddress|\Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array|null
     * @param $storeId
     * @return array|\Magento\Customer\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|/AvaTax/ValidAddress|\Magento\Customer\Api\Data\AddressInterface|\Magento\Quote\Api\Data\AddressInterface|\Magento\Sales\Api\Data\OrderAddressInterface|array|null
     * @throws AddressValidateException
     * @throws LocalizedException
     * @throws AvalaraConnectionException
     */
    public function validateAddress($addressInput, $storeId)
    {
        $validateRequestData = [
            'address' => $this->interactionAddress->getAddress($addressInput),
            'text_case' => $this->restConfig->getTextCaseMixed(),
        ];
        $validateRequest = $this->dataObjectFactory->create(['data' => $validateRequestData]);
        $validateResult = $this->addressService->validate($validateRequest, null, $storeId);

        $validAddresses = ($validateResult->hasData('validated_addresses'))
                            ? $validateResult->getData('validated_addresses') : null;
        
        if ($validAddresses === null ||
            !is_array($validAddresses) ||
            empty($validAddresses)
        ) {
            return null;
        }
        $validAddress = array_shift($validAddresses);

        $countyValue = '';
        if ($validAddress->getData()) {
            $validAddressData = $validAddress->getData();
            $countyValue = (isset($validAddressData[0]) && isset($validAddressData[0]['county']))
                            ? $validAddressData[0]['county'] : '';
        }

        // Convert data back to the type it was passed in as
        switch (true) {
            case ($addressInput instanceof \Magento\Customer\Api\Data\AddressInterface):
                $validAddress = $this->interactionAddress
                    ->convertAvaTaxValidAddressToCustomerAddress($validAddress, $addressInput);
                if (!empty($validAddress->getExtensionAttributes()) && !empty($countyValue)) {
                    $extAttr =  $validAddress->getExtensionAttributes();
                    $extAttr->setCounty($countyValue);
                }
                break;
            case ($addressInput instanceof \Magento\Quote\Api\Data\AddressInterface):
                $validAddress = $this->interactionAddress
                    ->convertAvaTaxValidAddressToQuoteAddress($validAddress, $addressInput);
                break;
            default:
                throw new LocalizedException(__(
                    'Input parameter "$addressInput" was not of a recognized/valid type: "%1".',
                    [
                        gettype($addressInput)
                    ]
                ));
        }
        return $validAddress;
    }
}
