<?php

declare(strict_types=1);

namespace Avalara\Excise\Observer\Sales;

class QuoteTotalsBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $shipAssignment = $observer->getEvent()->getshippingAssignment();
        $add = $shipAssignment->getShipping()->getAddress();
        if ($add->getCustomAttribute('county')) {
            $county = $add->getCustomAttribute('county');
            $arr = explode(PHP_EOL, (string)$county->getValue());
            if (count($arr) == 1 && strtolower($arr[0]) == "county") {
                $add->setCustomAttribute('county', "");
                $add->setData('county', "");
            }
            if (count($arr) > 1) {
                array_shift($arr);
                $add->setCustomAttribute('county', $arr[0]);
                $add->setData('county', $arr[0]);
            }
        }
        return $this;
    }
}
