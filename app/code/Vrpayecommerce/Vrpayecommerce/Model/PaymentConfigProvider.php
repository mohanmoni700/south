<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\RequestInterface;

class PaymentConfigProvider implements ConfigProviderInterface
{
    protected $paymentHelper;

    protected $assetRepo;

    protected $request;

    protected $brands = [
        'VISA',
        'MASTER',
        'AMEX',
        'DINERS',
        'JCB'
    ];

    protected $methodCodes = [
        'vrpayecommerce_ccsaved',
        'vrpayecommerce_creditcard',
        'vrpayecommerce_ddsaved',
        'vrpayecommerce_directdebit',
        'vrpayecommerce_giropay',
        'vrpayecommerce_klarnasliceit',
        'vrpayecommerce_klarnapaylater',
        'vrpayecommerce_paydirekt',
        'vrpayecommerce_paypal',
        'vrpayecommerce_paypalsaved',
        'vrpayecommerce_klarnaobt',
        'vrpayecommerce_easycredit',
        'vrpayecommerce_enterpay'
    ];

    protected $methods = [];

    /**
     *
     * @param PaymentHelper    $paymentHelper
     * @param Repository       $assetRepo
     * @param RequestInterface $request
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Repository $assetRepo,
        RequestInterface $request
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->assetRepo = $assetRepo;
        $this->request = $request;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * get configurations
     * @return [type]
     */
    public function getConfig()
    {
        $config = [];
        $configErrorEasyCredit = [];
        $mConfig = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                if ($code == 'vrpayecommerce_ccsaved' || $code == 'vrpayecommerce_creditcard') {
                    $selectedBrands = explode(' ', $this->methods[$code]->getBrand());
                    foreach ($this->brands as $brand) {
                        if (in_array($brand, $selectedBrands)) {
                            $display = 'block';
                        } else {
                            $display = 'none';
                        }
                        $asset = $this->createAsset(
                            'Vrpayecommerce_Vrpayecommerce::images/' . strtolower($brand) . '.png'
                        );
                        $config['payment']['vrpayecommerce']['logos'][$code][$brand] = [
                            'url' => $asset->getUrl(),
                            'height' => '35px',
                            'display' => $display
                        ];
                    }
                } else {
                    if ($code == 'vrpayecommerce_klarnapaylater') {
                        $config['payment']['vrpayecommerce']['logos'][$code] = $this->setMethodImage($code);
                        $mConfig = $this->setMethodConfig($code);
                    } elseif ($code == 'vrpayecommerce_klarnasliceit') {
                        $config['payment']['vrpayecommerce']['logos'][$code] = $this->setMethodImage($code);
                        $mConfig = $this->setMethodConfig($code);
                        $config['payment']['vrpayecommerce']['details'][$code]['flexible'] =
                            __('FRONTEND_TT_KLARNASLICEIT_FLEXIBEL');
                        $pClass = $this->methods[$code]->getPClassWithCalcMonthlyCost();
                        $config['payment']['vrpayecommerce']['details'][$code]['interest'] =
                            __('FRONTEND_TT_KLARNASLICEIT_INTEREST', $pClass['interestRate']);
                        $config['payment']['vrpayecommerce']['details'][$code]['monthlyFee'] =
                            __('FRONTEND_TT_KLARNASLICEIT_MONTHLY_FEE', $pClass['invoiceFee']);
                        $config['payment']['vrpayecommerce']['details'][$code]['monthlyPay'] =
                            __('FRONTEND_TT_KLARNASLICEIT_MONTHLY_PAY', $pClass['calcMonthlyCost']);
                    } elseif ($code =='vrpayecommerce_easycredit') {
                        $config['payment']['vrpayecommerce']['logos'][$code] = $this->setMethodImage($code);
                        $mConfig = $this->setMethodConfig($code);
                        $configErrorEasyCredit = $this->setConfigErrorEasyCredit($code);
                        $shopName = $this->methods[$code]->getConfigData('shop_name');
                        $config['payment']['vrpayecommerce']['details'][$code]['easycreditTerms'] = 
                                str_replace(
                                        "%x",
                                        $shopName,
                                        __('EASYCREDIT_FRONTEND_TERMS')
                                        );
                        $config['payment']['vrpayecommerce']['details'][$code]['easycreditTermError'] = __("ERROR_MESSAGE_EASYCREDIT_REQUIRED") ;
                    }
                    elseif ($code == 'vrpayecommerce_enterpay')
                    {
                        $config['payment']['vrpayecommerce']['logos'][$code] = $this->setMethodImage($code);
                        $config['payment']['vrpayecommerce']['title'][$code] = __('PAYMENT_TITLE_ENTERPAY');
                        $config['payment']['vrpayecommerce']['details'][$code]['redirect'] = __('DETAIL_MESSAGE_ENTERPAY_REDIRECT');
                    }
                    else {
                        $config['payment']['vrpayecommerce']['logos'][$code] = $this->setMethodImage($code);
                    }
                }
            }
        }
        $config = array_merge_recursive($config, $configErrorEasyCredit, $mConfig);
        return $config;
    }

    public function setMethodImage($code)
    {
        $asset = $this->createAsset('Vrpayecommerce_Vrpayecommerce::images/' . $this->methods[$code]->getLogo());
        $config = [
            'url' => $asset->getUrl(),
            'height' => '35px',
            'display' => 'block'
        ];

        return $config;
    }

    public function setMethodConfig($code)
    {
        $config['payment']['vrpayecommerce']['details'][$code]['merchantId'] =
            $this->methods[$code]->getConfigData('merchant_id');
        $config['payment']['vrpayecommerce']['details'][$code]['term1'] = __('FRONTEND_TT_KLARNA_TERM1');
        $config['payment']['vrpayecommerce']['details'][$code]['term2'] = __('FRONTEND_TT_KLARNA_TERM2');
        $config['payment']['vrpayecommerce']['details'][$code]['addressError']
            = __('ERROR_MESSAGE_BILLING_SHIPPING_NOTSAME');
        $config['payment']['vrpayecommerce']['details'][$code]['termError']
            = __('ERROR_MESSAGE_KLARNA_REQUIRED');
        $config['payment']['vrpayecommerce']['details'][$code]['easycreditConfirm'] =
            __('FRONTEND_EASYCREDIT_CONFIRM_BUTTON');

        return $config;
    }

    /**
     * create an asset
     * @param  string $fileId
     * @param  array  $params
     * @return object
     */
    public function createAsset($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);
        return $this->assetRepo->createAsset($fileId, $params);
    }

    public function setConfigErrorEasyCredit($code)
    {
        $config['payment']['vrpayecommerce']['details'][$code]['errorDoB'] = __('');
        $config['payment']['vrpayecommerce']['details'][$code]['errorGender'] = __('');
        $config['payment']['vrpayecommerce']['details'][$code]['errorAmount'] =
            "* ".__('ERROR_MESSAGE_EASYCREDIT_AMOUNT_NOTALLOWED');

        $errorGender = $this->methods[$code]->easyCreditGetGenderStatusError();

        if ($errorGender != '') {
            $config['payment']['vrpayecommerce']['details'][$code]['errorGender'] =
                "* ".__('ERROR_MESSAGE_EASYCREDIT_PARAMETER_GENDER');
        }
        $config['payment']['vrpayecommerce']['details'][$code]['addressErrorEasyCredit'] =
            "* ".__('ERROR_EASYCREDIT_BILLING_NOTEQUAL_SHIPPING');
        $config['payment']['vrpayecommerce']['details'][$code]['genderFemale'] = __('FRONTEND_GENDER_FEMALE');
        $config['payment']['vrpayecommerce']['details'][$code]['genderMale'] = __('FRONTEND_GENDER_MALE');

        return $config;
    }
}
