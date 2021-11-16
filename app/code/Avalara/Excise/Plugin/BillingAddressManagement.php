<?php
namespace Avalara\Excise\Plugin;

class BillingAddressManagement
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Set billing locality to quote object.
     *
     * @param \Psr\Log\LoggerInterface logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function beforeSetShippingAddress(
        \Magento\Quote\Model\Quote $subject,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        if ($address) {
            $address = $this->setAddressFields($address);
        }
        return [$address];
    }

    public function beforeSetBillingAddress(
        \Magento\Quote\Model\Quote $subject,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        if ($address) {
            $address = $this->setAddressFields($address);
        }
        return [$address];
    }

    private function setAddressFields($address)
    {
        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {
            try {
                $county = ($address->getCounty()) ? $address->getCounty() : $extAttributes->getCounty();
                if (isset($county)):
                    $address->setCounty($county);
                    $extAttributes->setCounty($county);
                endif;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $address;
    }
}
