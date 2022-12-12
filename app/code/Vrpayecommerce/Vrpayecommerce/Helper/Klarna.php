<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Helper;

class Klarna extends \Magento\Framework\App\Helper\AbstractHelper
{
	const KLARNAFLAG_CHECKOUT_PAGE = 0;
	const KLARNAFLAG_PRODUCT_PAGE = 1;
	const KLARNAPCLASS_ACCOUNT = 1;

	const COUNTRY_AT = 15;
	const COUNTRY_DK = 59;
	const COUNTRY_FI = 73;
	const COUNTRY_DE = 81;
	const COUNTRY_NL = 154;
	const COUNTRY_NO = 164;
	const COUNTRY_SE = 209;

	protected static $accuracy = 0.01;
	protected $klarnaUrl = 'payment.testdrive.klarna.com';

	public $curl;

    /**
    * [__construct description]
    * @param \Magento\Framework\App\Helper\Context       $context
    * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vrpayecommerce\Vrpayecommerce\Helper\Curl $curl
    ) {
        parent::__construct($context);        
        $this->curl = $curl;
    }


    /**
     * get a xmml request
     * @param  array $pClassParameters
     * @return string
     */
	protected function getXmlRequest($pClassParameters)
	{
	    $xmlRequest = '<?xml version="1.0" encoding="ISO-8859-1"?>
			            <methodCall>
			                <methodName>get_pclasses</methodName>
			                <params>
			                    <param>
			                        <value>
			                            <!-- proto_vsn -->
			                            <string>4.1</string>
			                        </value>
			                    </param>
			                    <param>
			                        <value>
			                            <!-- client_vsn -->
			                            <string>xmlrpc:vrpayvirtuell:1.1.0</string>
			                        </value>
			                    </param>
			                    <param>
			                        <value>
			                            <!-- merchant id (eid) -->
			                            <int>'.$pClassParameters['merchantId'].'</int>
			                        </value>
			                    </param>
			                    <param>
			                        <value>
			                            <!-- currency -->
			                            <int>'.$pClassParameters['currency'].'</int>
			                        </value>
			                    </param>
			                    <param>
			                        <value>
			                            <!-- shared_secret -->
			                            <string>'.$pClassParameters['digest'].'</string>
			                        </value>
			                    </param>
			                    <param>
			                        <value><!-- country -->
			                            <int>'.$pClassParameters['country'].'</int>
			                        </value>
			                    </param>
			                    <param>
			                        <value>
			                            <!-- language -->
			                            <int>'.$pClassParameters['language'].'</int>
			                        </value>
			                    </param>
			                </params>
			            </methodCall>';

	    return $xmlRequest;
	}

	/**
	 * get pclasses
	 * @param  string $pClassParameters
	 * @param  array $proxyParameters
     * @param  string $serverMode
     * @param  string $bearerToken
	 * @return array
	 */
	public function getPClasses($pClassParameters, $proxyParameters, $serverMode)
	{
		$xmlRequest = $this->getXmlRequest($pClassParameters);
		$xmlResponse = $this->curl->getXmlResponse($this->klarnaUrl, $xmlRequest, $proxyParameters['proxy'], $serverMode);

		if(!$xmlResponse['isValid']) {
			return array('success' => $xmlResponse['response']);
		} else {
			$pClasses = simplexml_load_string($xmlResponse['response']);
			$pClassData = $pClasses->params->param->value->array->data->value->array->data;
	        return $this->convertPClassDataToArray($pClassData);
		};
	}

	/**
	 * convert the pclass Data into array
	 * @param  object $pClassData
	 * @return array
	 */
    protected function convertPClassDataToArray($pClassData)
    {
        $pClass = array();

        $pClass['id'] = (string)$pClassData->value[0]->string;
        $pClass['description'] = (string)$pClassData->value[1]->string;
        $pClass['months'] = (string)$pClassData->value[2]->string;
        $pClass['start_fee'] = (string)$pClassData->value[3]->string;

        $invoiceFee = (float) $pClassData->value[4]->string / 100;
        $interestRate = (float) $pClassData->value[5]->string / 100;
        $pClass['invoice_fee'] = $invoiceFee;
        $pClass['interest_rate'] = $interestRate;

        $pClass['minimum_purchase'] = (string)$pClassData->value[6]->string;
        $pClass['country'] = (string)$pClassData->value[7]->int;
        $pClass['type'] = (string)$pClassData->value[8]->int;
        $pClass['expiry_date'] = (string)$pClassData->value[9]->string;

        return $pClass;
    }

    /**
     * get a pclass Digest
     * @param  array $pClassParameters
     * @return string
     */
    public function getPClassDigest($pClassParameters)
    {
        $merchantId = $pClassParameters['merchantId'];
        $currency = $pClassParameters['currency'];
        $sharedSecret = $pClassParameters['sharedSecret'];
        $pClassDigest = base64_encode(hash("sha512","$merchantId:$currency:$sharedSecret", true));

        return $pClassDigest;
    }

    /**
     *
     * @param  int $pval
     * @param  int $rate
     * @param  int $fee
     * @param  int $minpay
     * @param  int $payment
     * @param  int $months
     * @param  string $base
     * @return array
     */
	protected static function fulpacc(
        $pval, $rate, $fee, $minpay, $payment, $months, $base
    ) {
        $bal = $pval;
        $payarray = array();
        while (($months != 0) && ($bal > self::$accuracy)) {
            $interest = $bal * $rate / (100.0 * 12);
            $newbal = $bal + $interest + $fee;

            if ($minpay >= $newbal || $payment >= $newbal) {
                $payarray[] = $newbal;
                return $payarray;
            }

            $newpay = max($payment, $minpay);
            if ($base) {
                $newpay = max($newpay, $bal/24.0 + $fee + $interest);
            }

            $bal = $newbal - $newpay;
            $payarray[] = $newpay;
            $months--;
        }

        return $payarray;
    }

    /**
     *
     * @param  int $pval
     * @param  int $months
     * @param  int $rate
     * @return int
     */
	protected static function annuity($pval, $months, $rate)
    {
        if ($months == 0) {
            return $pval;
        }

        if ($rate == 0) {
            return $pval/$months;
        }

        $p = $rate / (100.0*12);
        return $pval * $p / (1 - pow((1+$p), -$months));
    }

    /**
     *
     * @param  int $sum
     * @param  array $pclass
     * @param  object $flags
     * @return void
     */
	protected static function getPayArray($sum, $pclass, $flags)
	{
	    $monthsfee = 0;
	    if ($flags === self::KLARNAFLAG_CHECKOUT_PAGE) {
	        $monthsfee = $pclass['invoiceFee'];
	    }
	    $startfee = 0;
	    if ($flags === self::KLARNAFLAG_CHECKOUT_PAGE) {
	        $startfee = $pclass['startFee'];
	    }

	    //Include start fee in sum
	    $sum += $startfee;

	    $base = ($pclass['type'] === self::KLARNAPCLASS_ACCOUNT);
	    $lowest = self::getLowestPaymentForAccount($pclass['country']);

	    if ($flags == self::KLARNAFLAG_CHECKOUT_PAGE) {
	        $minpay = ($pclass['type'] === self::KLARNAPCLASS_ACCOUNT) ? $lowest : 0;
	    } else {
	        $minpay = 0;
	    }

	    $payment = self::annuity(
	        $sum,
	        $pclass['months'],
	        $pclass['interestRate']
	    );

	    //Add monthly fee
	    $payment += $monthsfee;

	    return  self::fulpacc(
	        $sum,
	        $pclass['interestRate'],
	        $monthsfee,
	        $minpay,
	        $payment,
	        $pclass['months'],
	        $base
	    );
	}

	/**
	 * calculation a montly cost
	 * @param  int $sum
	 * @param  int $pclass
	 * @param  int $flags
	 * @return void
	 */
	public function calcMonthlyCost($sum, $pclass, $flags)
	{
	    if (is_numeric($sum) && (!is_int($sum) || !is_float($sum))) {
	        $sum = floatval($sum);
	    }

	    if (is_numeric($flags) && !is_int($flags)) {
	        $flags = intval($flags);
	    }

	    $payarr = self::getPayArray($sum, $pclass, $flags);
	    $value = 0;
	    if (isset($payarr[0])) {
	        $value = $payarr[0];
	    }

	    if (self::KLARNAFLAG_CHECKOUT_PAGE == $flags) {
	        return round($value, 2);
	    }
	    return self::pRound($value, $pclass['country']);
	}

	/**
	 * get a lowest payment for account
	 * @param  object $country
	 * @return int
	 */
	protected static function getLowestPaymentForAccount($country)
	{
	    switch ($country) {
	        case self::COUNTRY_SE:
	            return 50.0;
	        case self::COUNTRY_NO:
	            return 95.0;
	        case self::COUNTRY_FI:
	            return 8.95;
	        case self::COUNTRY_DK:
	            return 89.0;
	        case self::COUNTRY_DE:
	        case self::COUNTRY_AT:
	            return 6.95;
	        case self::COUNTRY_NL:
	            return 5.0;
	        default:
	            return 0.0;
	    }
	}

	/**
	 *
	 * @param  int $value
	 * @param  string $country
	 * @return int
	 */
	protected static function pRound($value, $country)
	{
	    $multiply = 1; //Round to closest integer
	    switch($country) {
	        case self::COUNTRY_FI:
	        case self::COUNTRY_DE:
	        case self::COUNTRY_NL:
	        case self::COUNTRY_AT:
	            $multiply = 10; //Round to closest decimal
	            break;
	    }

	    return floor(($value*$multiply)+0.5)/$multiply;
	}

}
