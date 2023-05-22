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
                    $str = explode('\n', (string)$county);
                    if (!empty($str[1])) {
                        $county = $str[1];
                    }
                }
                $this->logger->info('SHIPPING INFOMAG bill ship get county attr ' . $county);
                $extAttributes->setCounty($county);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
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
        }
    }
}
