<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Block\Product\View;

use Magento\Framework\View\Element\Template;

class KlarnaInfo extends Template
{
	protected $paymentMethod;

    public function __construct(
    	Template\Context $context,
    	array $data = [],
    	\Vrpayecommerce\Vrpayecommerce\Model\Method\Klarnasliceit $paymentMethod
    ) {
        parent::__construct($context, $data);
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * check if klarna installments is active
     * @return boolean
     */
	public function isKlarnasliceitActive()
	{
        return $this->paymentMethod->getConfigData('active');
	}

	/**
	 * get an interest rate
	 * @return string
	 */
	public function getInterestRate()
	{
        return $this->paymentMethod->getConfigData('pclass_interest_rate');
	}

	/**
	 * get an invoice fee
	 * @return string
	 */
	public function getInvoiceFee()
	{
		$invoiceFee = $this->paymentMethod->getConfigData('pclass_invoice_fee');
		$invoiceFee .= ' '.$this->paymentMethod->getKlarnaCurrencySymbol();
		return $invoiceFee;
	}
}