<?php

declare(strict_types=1);

namespace Alfakher\SalesExtended\ViewModel;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

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
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
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
                return $attr->getValue();
            }
        } catch (\Exception $e) {
        }
        return '';
    }
}
