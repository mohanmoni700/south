<?php

namespace Alfakher\CustomerCourierAccount\Plugin\Magento\Sales\Model\AdminOrder;

class Create
{

    /**
     * Around Import Post Data
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param callable $proceed
     * @param array $data
     */
    public function aroundImportPostData(\Magento\Sales\Model\AdminOrder\Create $subject, callable $proceed, $data)
    {
        $result = $proceed($data);

        if (isset($data['customer_courier_name'])) {
            $result->getQuote()->setCustomerCourierName($data['customer_courier_name']);
        }

        if (isset($data['customer_courier_account'])) {
            $result->getQuote()->setCustomerCourierAccount($data['customer_courier_account']);
        }

        return $result;
    }
}
