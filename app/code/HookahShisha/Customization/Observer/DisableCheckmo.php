<?php

namespace HookahShisha\Customization\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as state;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\ScopeInterface;

class DisableCheckmo implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /**
     * @param State $state
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        State $state,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute
     *
     * @param mixed $observer
     */
    public function execute(Observer $observer)
    {
        $isActive = $this->getStoreConfig('graphql_payment/payment_details/disable_payment_garphql');
        $methods = $this->getStoreConfig('graphql_payment/payment_details/payment_needs_disable');
        $paymentMethods = explode(',', $methods);
        $paymentCode = $observer->getEvent()->getMethodInstance()->getCode();
        $checkResult = $observer->getEvent()->getResult();
        if ($isActive == 1 && in_array($paymentCode, $paymentMethods)) {
            $checkResult->setData('is_available', false);
        }
    }

    /**
     * Method For get Configuration Values
     *
     * @param string $path
     */
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
