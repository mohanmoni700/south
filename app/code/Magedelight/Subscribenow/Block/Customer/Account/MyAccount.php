<?php

namespace Magedelight\Subscribenow\Block\Customer\Account;

use Magedelight\Subscribenow\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;

class MyAccount extends Current
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Context $context,
        Data $helper,
        Session $customerSession,
        DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    protected function _toHtml()
    {
        $allowedCustomerGroups  = $this->helper->getAllowedCustomerGroups();
        $currentCustomerGroup = $this->customerSession->getCustomerGroupId();
        if (in_array($currentCustomerGroup, $allowedCustomerGroups)) {
            return parent::_toHtml();
        }
        return null;
    }
}
