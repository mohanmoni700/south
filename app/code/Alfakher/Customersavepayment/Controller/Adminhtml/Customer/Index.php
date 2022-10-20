<?php

namespace Alfakher\Customersavepayment\Controller\Adminhtml\Customer;

class Index extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * [execute]
     *
     * @return mixed
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
