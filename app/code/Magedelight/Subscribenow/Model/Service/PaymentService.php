<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Service;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class PaymentService
 * @package Magedelight\Subscribenow\Model\Service
 */
class PaymentService
{
    const MODEL_PATH = "Magedelight\Subscribenow\Model\Service\Payment\\";

    /**
     * Define Used Payment Method
     */
    const MD_AUTHORIZECIM = 'md_authorizecim';
    /**
     * This method commented for new magedelight stripe 3x compatibility
     */
    const MD_STRIPE_CARD = 'md_stripe_cards';
    const MD_FIRSTDATA = 'md_firstdata';
    const MD_MONERIS = 'md_moneris';
    const MD_MONERISCA = 'md_monerisca';
    const MD_CYBERSOURCE = 'magedelight_cybersource';
    const MD_EWALLET = 'magedelight_ewallet';
    const COD = 'cashondelivery';
    const OPS_CC = 'ops_cc';
    const CYBERSOURCEOP = 'cybersourcesop';
    const CYBERSOURCEOP_CC_VAULT = 'cybersourcesop_cc_vault';
    const VAULT_PAYMENT = 'vault';

    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getClassInfoVars()
    {
        return [
            self::MD_AUTHORIZECIM => self::MODEL_PATH . 'Authorizecim',
            // SELF::MD_STRIPE_CARD => self::MODEL_PATH . 'Stripe',
            self::MD_FIRSTDATA => self::MODEL_PATH . 'Firstdata',
            self::MD_MONERIS => self::MODEL_PATH . 'Moneris',
            self::MD_MONERISCA => self::MODEL_PATH . 'Monerisca',
            self::MD_CYBERSOURCE => self::MODEL_PATH . 'CyberSource',
            self::MD_EWALLET => self::MODEL_PATH . 'Ewallet',
            self::COD => self::MODEL_PATH . 'COD',
            self::OPS_CC => self::MODEL_PATH . 'Ingenico',
            self::CYBERSOURCEOP => self::MODEL_PATH . 'Cybersourcesop',
            self::CYBERSOURCEOP_CC_VAULT => self::MODEL_PATH . 'Cybersourcesop',
            self::VAULT_PAYMENT => self::MODEL_PATH . 'VaultPayment'
        ];
    }

    public function getClassCardCollectionVars()
    {
        return [
            self::MD_FIRSTDATA => \Magedelight\Firstdata\Model\ResourceModel\Cards\CollectionFactory::class,
            self::MD_MONERIS => \Magedelight\Moneris\Model\ResourceModel\Cards\CollectionFactory::class,
            self::MD_MONERISCA => \Magedelight\Monerisca\Model\ResourceModel\Cards\CollectionFactory::class,
            self::MD_AUTHORIZECIM => \Magedelight\Authorizecim\Model\ResourceModel\Cards\CollectionFactory::class,
            // SELF::MD_STRIPE_CARD => \Magedelight\Stripe\Model\ResourceModel\Cards\CollectionFactory::class,
            self::MD_CYBERSOURCE => \Magedelight\Cybersource\Model\ResourceModel\Cards\CollectionFactory::class,
            self::OPS_CC => \Netresearch\OPS\Model\ResourceModel\Alias\CollectionFactory::class
        ];
    }

    private function getClassInfo($key = null)
    {
        $data = $this->getClassInfoVars();
        if ($key && !empty($data[$key])) {
            return $data[$key];
        }
        return null;
    }

    private function getCardCollectionClass($key = null)
    {
        $data = $this->getClassCardCollectionVars();
        if ($key && !empty($data[$key])) {
            return $data[$key];
        }
        return null;
    }

    /**
     * Create Class
     * @param $class
     * @param array $array
     */
    private function create($class, $array = [])
    {
        return $this->objectManager->create($class, $array);
    }

    private function getClassObject($code, $order = null, $factory = null, $data = [])
    {
        return $this->create(
            $this->getClassInfo($code),
            [
                'order' => $order,
                'cardCollectionFactory' => $factory,
                'data' => $data
            ]
        );
    }

    /**
     * Set Payment Data to Subscription On
     * Order Place Time.
     *
     * @param $order
     * @return mixed
     */
    public function get($order)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        if ($order->hasBaseUsedCheckoutWalletAmout() && $order->getBaseUsedCheckoutWalletAmout() > 0) {
            $paymentMethod = self::MD_EWALLET;
        }
        $code = $this->getClassInfo($paymentMethod) ? $paymentMethod : self::VAULT_PAYMENT;

        return $this->getClassObject($code, $order, null, []);
    }

    /**
     * Get Current Method Payment Service Object
     * On Subscription View time return current payment data
     *
     * @param string $paymentMethod
     * @param string $token
     * @param int|null $customerId
     *
     * @return object|null
     */
    public function getByPaymentCode($paymentMethod, $token, $customerId = null)
    {
        $code = self::VAULT_PAYMENT;
        $data = ['token' => $token, 'method_code' => $paymentMethod, 'customer_id' => $customerId];
        if ($this->getClassInfo($paymentMethod)) {
            $code = $paymentMethod;
            $data = ['token' => $token, 'customer_id' => $customerId];
        }
        $factory = $this->getCardCollectionFactory($paymentMethod);

        return $this->getClassObject($code, null, $factory, $data);
    }

    /**
     * On Generate Order Time
     * Return Payment Object
     *
     * @param object $subscription
     * @return object|null
     */
    public function getBySubscription($subscription)
    {
        $paymentMethod = $subscription->getPaymentMethodCode();
        $code = $this->getClassInfo($paymentMethod) ? $paymentMethod : self::VAULT_PAYMENT;
        $data = ['subscription_instance' => $subscription];
        $factory = $this->getCardCollectionFactory($paymentMethod);

        return $this->getClassObject($code, null, $factory, $data);
    }

    /**
     * Get Card Collection Factory Object
     * @param string $code
     * @return object|null
     */
    private function getCardCollectionFactory($code)
    {
        if ($class = $this->getCardCollectionClass($code)) {
            return $this->create($class);
        }

        return null;
    }
}
