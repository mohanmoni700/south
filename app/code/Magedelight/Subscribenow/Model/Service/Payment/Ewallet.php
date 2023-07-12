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

namespace Magedelight\Subscribenow\Model\Service\Payment;

use Magedelight\Subscribenow\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

class Ewallet extends AbstractPayment
{
    const EWALLET_TITLE = 'md_wallet/general/ewallet_title';
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper,
        EncryptorInterface $encryptor,
        DataObjectFactory $dataObject,
        ObjectManagerInterface $objectManager,
        Customer $customer,
        PaymentTokenManagementInterface $paymentToken,
        $cardCollectionFactory,
        $order,
        array $data = []
    ) {
        parent::__construct($encryptor, $dataObject, $objectManager, $customer, $paymentToken, $cardCollectionFactory, $order, $data);
        $this->helper = $helper;
    }

    public function getTitle()
    {
        return $this->helper->getEWalletPaymentTitle();
    }

    public function checkBalance($grandTotal)
    {
        $subscription = $this->getSubscription();

        $walletModel = $this->create(\Magedelight\EWallet\Model\ResourceModel\Wallet\Collection::class)
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $subscription->getCustomerId())
            ->getFirstItem();
        $useramount = $walletModel['remaining_wallet_amount'];
        if ($useramount > $grandTotal) {
            return true;
        }
        return false;
    }

    /**
     * return \Magedelight\Subscribenow\Model\ProductSubscribers
     */
    private function getSubscription()
    {
        return $this->data['subscription_instance'];
    }

    /**
     * @return mixed
     */
    public function getMethodCode()
    {
        return 'magedelight_ewallet';
    }

    public function getImportData()
    {
        return ['method' => 'free'];
    }
}
