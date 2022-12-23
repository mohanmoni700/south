<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model\Method;

class Klarnasliceit extends \Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod
{
    protected $_code= 'vrpayecommerce_klarnasliceit';
    protected $brand = 'KLARNA_INSTALLMENTS';
    protected $methodTitle = 'FRONTEND_PM_KLARNASLICEIT';
    protected $paymentType = 'PA';
    protected $logo = 'klarnasliceit_en.png';
    protected $logoDe = 'klarnasliceit_de.png';
    protected $isServerToServer = true;
    protected $isLogoDe = true;

    /**
     * get the klarna pclass
     * @return array
     */
    public function getKlarnaPClass()
    {
        $klarnaPClass = array();

        $klarnaPClass['id'] = $this->getConfigData('pclass_id');
        $klarnaPClass['months'] = $this->getConfigData('pclass_months');
        $klarnaPClass['startFee'] = $this->getConfigData('pclass_start_fee');
        $klarnaPClass['invoiceFee'] = $this->getConfigData('pclass_invoice_fee');
        $klarnaPClass['interestRate'] = $this->getConfigData('pclass_interest_rate');
        $klarnaPClass['country'] = (int) $this->getConfigData('pclass_country');
        $klarnaPClass['type'] = (int) $this->getConfigData('pclass_type');

        return $klarnaPClass;
    }

    /**
     * get the pclass with calculation a montly cost
     * @return array
     */
    public function getPClassWithCalcMonthlyCost()
    {
        $amount = $this->getKlarnaMonthlyCostAmount();
        $pClass = $this->getKlarnaPClass();

        $pClass['calcMonthlyCost'] = $this->getKlarnaHelper()->calcMonthlyCost($amount, $pClass, 0);

        $pClass['invoiceFee'] .= ' '.$this->getKlarnaCurrencySymbol();
        $pClass['calcMonthlyCost'] .= ' '.$this->getKlarnaCurrencySymbol();

        return $pClass;
    }

    /**
     * get the klarna currency
     * @return string
     */
    public function getKlarnaCurrency()
    {
        $currencyCode = $this->getConfigData('currency');

        switch ($currencyCode) {
            case '0':
                $currency = 'SEK';
                break;
            case '1':
                $currency = 'NOK';
                break;
            case '2':
                $currency = 'EUR';
                break;
            case '3':
                $currency = 'DKK';
                break;
            default:
                $currency = '';
                break;
        }

        $baseCode = $this->getStore()->getBaseCurrencyCode();
        $allowedCurrencies = $this->getCurrencyDirectory()->getConfigAllowCurrencies();
        $rates = $this->getCurrencyDirectory()->getCurrencyRates($baseCode, array_values($allowedCurrencies));

        foreach ($rates as $key => $value) {
            if ($key == $currency) {
                return $currency;
            }
        }

        return $this->getQuoteCurrency();
    }

    /**
     * get the klarna currency symbol
     * @return string
     */
    public function getKlarnaCurrencySymbol()
    {
        $klarnaCurrency = $this->getKlarnaCurrency();
        $currencySymbol = $this->getLocaleCurrency()->getCurrency($klarnaCurrency)->getSymbol();
        if (isset($currencySymbol)) {
            return $currencySymbol;
        }
        return $klarnaCurrency;
    }

    /**
     * get a locale currency
     * @return object
     */
    public function getLocaleCurrency()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $localeCurrency = $objectManager->create('\Magento\Framework\Locale\CurrencyInterface');

        return $localeCurrency;
    }

    /**
     * get the klarna helper
     * @return object
     */
    public function getKlarnaHelper()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $klarnaHelper = $objectManager->create('\Vrpayecommerce\Vrpayecommerce\Helper\Klarna');

        return $klarnaHelper;
    }

    /**
     * get a currency directory
     * @return [type]
     */
    public function getCurrencyDirectory()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directoryCurrency = $objectManager->create('\Magento\Directory\Model\Currency');

        return $directoryCurrency;
    }

    /**
     * get a helper directory
     * @return [type]
     */
    public function getHelperDirectory()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directoryCurrency = $objectManager->create('\Magento\Directory\Helper\Data');

        return $directoryCurrency();
    }

    /**
     * get a store
     * @return object
     */
    public function getStore()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');

        return $storeManager->getStore();
    }

    /**
     * get a quote
     * @return object
     */
    public function getQuote()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->create('\Magento\Checkout\Model\Cart');

        return $cart->getQuote();
    }

    /**
     * get a quote currency
     * @return object
     */
    public function getQuoteCurrency()
    {
        $quote = $this->getQuote();

        return $quote->getQuoteCurrencyCode();
    }

    /**
     * get a quote grand total
     * @return object
     */
    public function getQuoteGrandTotal()
    {
        $quote = $this->getQuote();

        return $quote->getGrandTotal();
    }

    /**
     * get the klarna monthly cost amount
     * @return object
     */
    public function getKlarnaMonthlyCostAmount()
    {
        $quoteCurrency = $this->getQuoteCurrency();
        $quoteGrandTotal = $this->getQuoteGrandTotal();
        $klarnaCurrency = $this->getKlarnaCurrency();

        if ($quoteCurrency != $klarnaCurrency) {
            return $this->getHelperDirectory()->currencyConvert($quoteGrandTotal, $quoteCurrency, $klarnaCurrency);
        }

        return $quoteGrandTotal;
    }


}
