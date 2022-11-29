<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Service\Payment;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use PayPal\Braintree\Gateway\Command\GetPaymentNonceCommand;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/**
 * Class VaultPayment
 * Make magento's default vault method compitable to SubscribeNow.
 *
 * @package Magedelight\Subscribenow\Model\Service\Payment
 */
class VaultPayment
{
    const CARD_PREFIX = "XXXX-%s";

    /**
     * @var Magento\Sales\Model\Order
     */
    private $order;
    
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    
    /**
     * @var array
     */
    private $data;
    
    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;
    
    /**
     * @var GetPaymentNonceCommand
     */
    private $paymentNonce;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * VaultPayment constructor.
     *
     * @param EncryptorInterface $encryptor
     * @param PaymentTokenManagementInterface $paymentToken
     * @param PaymentTokenRepositoryInterface $repository
     * @param GetPaymentNonceCommand $paymentNonce
     * @param Json $json
     * @param $order
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        EncryptorInterface $encryptor,
        PaymentTokenManagementInterface $paymentToken,
        PaymentTokenRepositoryInterface $repository,
        GetPaymentNonceCommand $paymentNonce,
        Json $json,
        $order,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->encryptor = $encryptor;
        $this->tokenManagement = $paymentToken;
        $this->paymentTokenRepository = $repository;
        $this->paymentNonce = $paymentNonce;
        $this->order = $order;
        $this->data = $data;
        $this->json = $json;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    private function generatePublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();
        if ($paymentToken->getCustomerId()) {
            $hashKey = $paymentToken->getCustomerId();
        }

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }
    
    /**
     * Return Payment token
     *
     * @return string
     */
    public function getPaymentToken()
    {
        $publicHash = null;
        $payment = $this->getPaymentInformation();
        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        $additionalInfo = $payment->getAdditionalInformation();

        if ($additionalInfo && !empty($additionalInfo['public_hash'])) {
            $publicHash = $additionalInfo['public_hash'];
        } elseif ($paymentToken !== null) {
            $paymentToken->setCustomerId($this->order->getCustomerId());
            $paymentToken->setIsActive(true);
            $paymentToken->setPaymentMethodCode($payment->getMethod());
            $publicHash = $this->generatePublicHash($paymentToken);
        }

        if ($publicHash) {
            $publicHash = $this->encryptor->encrypt($publicHash);
        }

        return $publicHash;
    }
    
    public function getTitle()
    {
        return $this->getPaymentInformation()->getAdditionalInformation('method_title');
    }
    
    /**
     * Return Payment Method code
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->getPaymentInformation()->getMethod();
    }
    
    /**
     * Return payment Details
     *
     * @return object
     */
    private function getPaymentInformation()
    {
        return $this->order->getPayment();
    }

    /**
     * Get Last 4 Digit of Card
     *
     * @return string\null
     */
    private function getCardDigit($data = null)
    {
        $cardNumber = null;
        if ($data && is_array($data)) {
            if (!empty($data['maskedCC'])) {
                $cardNumber = $data['maskedCC'];
            }

            if (!empty($data['cc_last_4'])) {
                $cardNumber = $data['cc_last_4'];
            }

            if ($cardNumber) {
                $cardNumber = sprintf(self::CARD_PREFIX, $cardNumber);
            }
        }
        return $cardNumber;
    }

    /**
     * Get Active Card Number
     * @return string
     */
    public function getCardInfo()
    {
        $publicHash = $this->getDecryptedToken($this->data['token']);
        $customerId = $this->data['customer_id'];

        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);
        if ($paymentToken) {
            $activeCard = $paymentToken->getIsActive();
            if ($activeCard) {
                $details = $this->json->unserialize($paymentToken->getDetails());
                return $this->getCardDigit($details);
            }
        }
        
        return null;
    }
    
    /**
     * Get Saved Cards
     * @return array
     */
    public function getSavedCards()
    {
        $cards = [];
        $customerId = $this->data['customer_id'];
        $code = $this->data['method_code'];
        $token = $this->getDecryptedToken($this->data['token']);

        if ($customerId) {
            $customers = $this->getListByCustomerId($customerId, $code);
            foreach ($customers as $card) {
                if ($card->getIsActive()) {
                    $detail = $this->json->unserialize($card->getDetails());
                    $cardToken = $card->getPublicHash();

                    if (!$cardToken) {
                        continue;
                    }

                    $cards[$this->encryptor->encrypt($cardToken)] = [
                        'is_current' =>  $cardToken == $token ? 1 : 0,
                        'label' => $this->getCardDigit($detail)
                    ];
                }
            }
        }
        
        return $cards;
    }

    /**
     * @return array
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function getImportData()
    {
        /** @var \Magedelight\Subscribenow\Model\ProductSubscribers $subscription */
        $subscription = $this->data['subscription_instance'];
        $token = $this->getDecryptedToken($subscription->getPaymentToken());
        $paymentMethod = $subscription->getPaymentMethodCode();

        if ($paymentMethod == 'braintree_paypal') {
            $paymentMethod = 'braintree_paypal_vault';
        } elseif (strpos($paymentMethod, '_cc') !== false) {
            $paymentMethod = $paymentMethod . '_vault';
        } elseif (strpos($paymentMethod, '_cc_vault') === false) {
            $paymentMethod = $paymentMethod . '_cc_vault';
        }

        $paymentDetails = [
            'method' => $paymentMethod,
            'is_active_payment_token_enabler' => 'true',
            'public_hash' => $token
        ];

        if ($paymentMethod == 'braintree_cc_vault' || $paymentMethod == 'braintree_paypal_vault') {
            $data = [
                'public_hash' => $token,
                'customer_id' => $subscription->getCustomerId()
            ];
            $result = $this->paymentNonce->execute($data)->get();
            $paymentDetails['payment_method_nonce'] = $result['paymentMethodNonce'];
        }

        return $paymentDetails;
    }

    /**
     * Get Decrypt Token
     * @param $token
     * @return string
     */
    private function getDecryptedToken($token)
    {
        if ($token) {
            return $this->encryptor->decrypt($token);
        }
    }

    /**
     * Lists payment tokens that match specified search criteria.
     *
     * @param int $customerId Customer ID.
     * @param string $paymentMethod Payment Method.
     * @return PaymentTokenInterface[]
     */
    public function getListByCustomerId($customerId, $paymentMethod)
    {
        $customer[] = $this->filterBuilder
            ->setField(PaymentTokenInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->create();

        $payment[] = $this->filterBuilder
            ->setField(PaymentTokenInterface::PAYMENT_METHOD_CODE)
            ->setValue($paymentMethod)
            ->create();

        return $this->paymentTokenRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilters($customer)
                ->addFilters($payment)
                ->create()
        )->getItems();
    }
}
