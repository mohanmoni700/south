<?php
namespace Alfakher\AddtocartPriceHide\Observer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddHandle implements ObserverInterface
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @inheritDoc
     */
    public function __construct(CustomerSession $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getEvent()->getLayout();
        if (!$this->customerSession->isLoggedIn()) {
            $layout->getUpdate()->addHandle('guest_user');
        } else {
            $layout->getUpdate()->addHandle('login_user');
        }
    }
}
