<?php
namespace Alfkaher\SlopePayment\Plugin\Magento\Quote\Model\Payment;

class SaveSlopeAdditionalInfo
{
    /**
     * @param \Magento\Quote\Model\Quote\Payment $subject
     * @param array $data
     * @return array
     */
    public function beforeImportData(\Magento\Quote\Model\Quote\Payment $subject, array $data)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/slopessss.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(print_r($data, true));
        /* if (array_key_exists('additional_information', $data)) {
            $subject->setAdditionalInformation($data['additional_information']);
        } */

        return [$data];
    }
}
