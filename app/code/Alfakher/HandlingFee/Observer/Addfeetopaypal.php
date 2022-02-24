<?php

namespace Alfakher\HandlingFee\Observer;

/**
 * Adding additional fee to paypal payload
 *
 * @author af_bv_op
 */
use Alfakher\HandlingFee\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Checkout\Model\Session;

class Addfeetopaypal implements ObserverInterface
{

    /**
     * @var $checkout
     */
    protected $checkout;

    /**
     * @var $helper
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Session $checkout
     * @param Data $helper
     */
    public function __construct(
        Session $checkout,
        Data $helper
    ) {
        $this->checkout = $checkout;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isModuleEnabled()) {
            return $this;
        }
        $cart = $observer->getEvent()->getCart();
        $quote = $this->checkout->getQuote();
        $customAmount = $quote->getFee();
        $label = "Handling Fee";
        if ($customAmount) {
            $cart->addCustomItem($label, 1, $customAmount, $label);
        }
    }
}
