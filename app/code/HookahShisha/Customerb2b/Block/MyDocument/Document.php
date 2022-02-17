<?php

namespace HookahShisha\Customerb2b\Block\MyDocument;

class Document extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }
}
