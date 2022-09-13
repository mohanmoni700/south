<?php

namespace Alfakher\Seamlesschex\Observer;

class AssignData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $method = $observer->getData('method');
        $payment = $observer->getData('payment_model');
        if ($payment === null) {
            $payment = $method->getInfoInstance();
        }
        $data = $observer->getData('data');

        if ($data->hasData('additional_data')) {
            foreach ($data->getData('additional_data') as $key => $value) {
                if ($data->getData($key) == false) {
                    $data->setData($key, $value);
                }
            }
        }

        $this->assignStandardData($payment, $data, $method);
    }

    /**
     * Assign standard data
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param \Magento\Framework\DataObject $data
     * @param \Magento\Payment\Model\MethodInterface $method
     */
    protected function assignStandardData(
        \Magento\Payment\Model\InfoInterface $payment,
        \Magento\Framework\DataObject $data,
        \Magento\Payment\Model\MethodInterface $method
    ) {
        $payment->setData('ach_account_number', $data->getData('accountnumber'));
        $payment->setData('ach_routing_number', $data->getData('routingnumber'));
        $payment->setData('ach_check_number', $data->getData('checknumber'));
    }
}
