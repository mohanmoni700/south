<?php

namespace Avalara\Excise\Plugin;

class ShippingInformationManagementPlugin
{
    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function beforeAssign(
        \Magento\Quote\Model\ShippingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        $extAttributes = $address->getExtensionAttributes();
        $this->logger->info(' SHIPPING INFOMAG ship address ID ' . $address->getId());
        if (!empty($extAttributes)) {
            try {
                $county = $extAttributes->getCounty();
                if ($county) {
                    $str = explode('\n', $county);
                    if (!empty($str[1])) {
                        $county = $str[1];
                    }
                }
                $this->logger->info('SHIPPING INFOMAG bill ship get county attr ' . $county);
                $extAttributes->setCounty($county);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
