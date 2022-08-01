<?php

namespace HookahShisha\Customization\Observer;

use Magento\Framework\App\State as state;
use Magento\Framework\Event\Observer;

class DisableCheckmo implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @param State $state
     */
    public function __construct(
        State $state
    ) {
        $this->state = $state;
    }

    /**
     * Execute
     *
     * @param mixed $observer
     */
    public function execute(Observer $observer)
    {
        $paymentCode = $observer->getEvent()->getMethodInstance()->getCode();
        $checkResult = $observer->getEvent()->getResult();
        if ($paymentCode == 'checkmo') {
            if ($this->state->getAreaCode() == 'graphql') {
                $checkResult->setData('is_available', false);
            }
        }
    }
}
