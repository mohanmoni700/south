<?php

declare(strict_types=1);

namespace GlobalHookah\SalesExtended\ViewModel;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class Address implements ArgumentInterface
{

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger
    ) {
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }


    /**
     * Get Location type data
     *
     * @param  int|null $addrId
     * @return mixed|string
     */
    public function getLocationType($addrId)
    {
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        try {
            $address = $this->addressRepository->getById($addrId);
            if ($attr = $address->getCustomAttribute('destination_type')) {
                return ucfirst(strtolower($attr->getValue()));
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return '';
    }
}
