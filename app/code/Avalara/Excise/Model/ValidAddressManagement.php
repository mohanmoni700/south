<?php

namespace Avalara\Excise\Model;

use Avalara\Excise\Api\ValidAddressManagementInterface;
use Avalara\Excise\Framework\Interaction\Address\Validation as ValidationInteraction;
use Magento\Customer\Api\Data\AddressInterface;
use Avalara\Excise\Exception\AvalaraConnectionException;
use Psr\Log\LoggerInterface;

class ValidAddressManagement implements ValidAddressManagementInterface
{
    /**
     * @var \Avalara\Excise\Framework\Interaction\Address\Validation
     */
    protected $validationInteraction = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ValidAddressManagement constructor.
     * @param ValidationInteraction $validationInteraction
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger,
        ValidationInteraction $validationInteraction,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function saveValidAddress(AddressInterface $address, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        try {
            return $this->validationInteraction->validateAddress($address, $storeId);
        } catch (AvalaraConnectionException $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
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
            return __('Address validation connection error')->getText();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
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
            return $e->getMessage();
        }
    }
}
