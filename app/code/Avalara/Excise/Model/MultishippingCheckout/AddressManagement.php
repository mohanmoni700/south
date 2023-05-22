<?php

namespace Avalara\Excise\Model\MultishippingCheckout;

use Avalara\Excise\Api\Data\AddressInterface;
use Avalara\Excise\Api\MultishippingAddressManagementInterface;
use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\Region;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\Quote\TotalsCollector;
use \Psr\Log\LoggerInterface;

class AddressManagement implements MultishippingAddressManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Region
     */
    private $region;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionInterfaceFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @var AddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Region $region
     * @param AddressRepositoryInterface $addressRepository
     * @param RegionInterfaceFactory $regionInterfaceFactory
     * @param Session $customerSession
     * @param TotalsCollector $totalsCollector
     * @param AddressFactory $quoteAddressFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Region $region,
        AddressRepositoryInterface $addressRepository,
        RegionInterfaceFactory $regionInterfaceFactory,
        Session $customerSession,
        TotalsCollector $totalsCollector,
        AddressFactory $quoteAddressFactory,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->region = $region;
        $this->addressRepository = $addressRepository;
        $this->regionInterfaceFactory = $regionInterfaceFactory;
        $this->customerSession = $customerSession;
        $this->totalsCollector = $totalsCollector;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->logger = $logger;
    }

    /**
     * @param AddressInterface $address
     * @return bool
     */
    public function execute(AddressInterface $address): bool
    {
        try {
            $customerAddress = $this->addressRepository->getById($address->getCustomerAddressId());
            $customerAddress->setCity($address->getCity());
            $customerAddress->setPostcode($address->getPostcode());
            $customerAddress->setStreet([$address->getStreet()]);

            $customerRegion = $this->regionInterfaceFactory->create();
            $newRegion = $this->region->loadByName($address->getRegion(), $customerAddress->getCountryId());
            $customerRegion->setRegionCode($newRegion->getCode());
            $customerRegion->setRegion($newRegion->getName());
            $customerRegion->setRegionId($newRegion->getId());

            $customerAddress->setRegion($customerRegion);
            $customerAddress->setRegionId($customerRegion->getRegionId());

            if (empty($customerAddress->getCustomAttribute('county'))) {
                // create the custom attribute if missing in old addresses
                $customerAddress->setCustomAttribute('county', "");
            }

            if (!empty($customerAddress->getCustomAttribute('county'))) {
                $customerAddress->getCustomAttribute('county')->setValue($address->getCounty());
            }

            $this->addressRepository->save($customerAddress);

            $quote = $this->quoteRepository->get($address->getQuoteId());
            if ($address->getAddressType() == \Magento\Sales\Model\Order\Address::TYPE_SHIPPING) {
                $this->updateQuoteCustomerShippingAddress(
                    $quote,
                    $address->getCustomerAddressId(),
                    $address->getCounty()
                );
            } else {
                $billingAddress = $this->quoteAddressFactory->create()->importCustomerAddressData($customerAddress);
                $quote->setBillingAddress($billingAddress);
                $this->quoteRepository->save($quote);
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical('Multi ship address management', ['exception' => $e]);
            // code to add CEP logs for exception
            try {
                $functionName = __METHOD__;
                $operationName = get_class($this);   
                // @codeCoverageIgnoreStart             
                $this->logger->logDebugMessage(
                    $functionName,
                    $operationName,
                    $e
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
        } catch (LocalizedException $e) {
            $this->logger->critical('Multi ship address management', ['exception' => $e]);
            // code to add CEP logs for exception
            try {
                $functionName = __METHOD__;
                $operationName = get_class($this);
                // @codeCoverageIgnoreStart                
                $this->logger->logDebugMessage(
                    $functionName,
                    $operationName,
                    $e
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
        }

        return true;
    }

    /**
     * @param $quote
     * @param $addressId
     * @param $county
     * @return $this
     * @throws LocalizedException
     */
    private function updateQuoteCustomerShippingAddress($quote, $addressId, $county): AddressManagement
    {
        if (!$this->isAddressIdApplicable($addressId)) {
            throw new LocalizedException(__('Verify the shipping address information and continue.'));
        }
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (Exception $e) {
            $this->logger->critical('Multi ship address management', ['exception' => $e]);
            // code to add CEP logs for exception
            try {
                $functionName = __METHOD__;
                $operationName = get_class($this);   
                // @codeCoverageIgnoreStart             
                $this->logger->logDebugMessage(
                    $functionName,
                    $operationName,
                    $e
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
        }
        if (isset($address)) {
            $quoteAddress = $quote->getShippingAddressByCustomerAddressId($addressId);
            $quoteAddress->setCounty($county);
            $quoteAddress->setCollectShippingRates(true)->importCustomerAddressData($address);
            $this->totalsCollector->collectAddressTotals($quote, $quoteAddress);
            $this->quoteRepository->save($quote);
        }

        return $this;
    }

    /**
     * @param $addressId
     * @return bool
     */
    private function isAddressIdApplicable($addressId): bool
    {
        $applicableAddressIds = array_map(function ($address) {
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            return $address->getId();
        }, $this->getCustomer()->getAddresses());

        return !is_numeric($addressId) || in_array($addressId, $applicableAddressIds);
    }

    /**
     * @return CustomerInterface
     */
    private function getCustomer(): CustomerInterface
    {
        return $this->customerSession->getCustomerDataObject();
    }
}
