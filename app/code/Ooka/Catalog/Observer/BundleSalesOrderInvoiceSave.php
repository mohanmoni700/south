<?php

declare(strict_types=1);

namespace Ooka\Catalog\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CustomerBalance\Helper\Data;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\CustomerBalance\Model\ResourceModel\Balance as ResourceModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class BundleSalesOrderInvoiceSave implements ObserverInterface
{
    const ANNUAL_BUNDLE_CONFIG = 'annual_bundle/general/bundle_config';
    const IS_NOTIFY_CUSTOMER = 'annual_bundle/general/is_notify';

    protected $scopeConfig;

    protected BalanceFactory $balanceFactory;

    protected StoreManagerInterface $storeManager;
    protected Data $customerBalanceData;

    protected LoggerInterface $logger;
    private ResourceModel $resourceModel;
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param BalanceFactory $balanceFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param ResourceModel $resourceModel
     * @param Data $customerBalanceData
     */
    public function __construct(
        ScopeConfigInterface        $scopeConfig,
        BalanceFactory              $balanceFactory,
        StoreManagerInterface       $storeManager,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface             $logger,
        ResourceModel               $resourceModel,
        Data                        $customerBalanceData
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->balanceFactory = $balanceFactory;
        $this->storeManager = $storeManager;
        $this->customerBalanceData = $customerBalanceData;
        $this->logger = $logger;
        $this->resourceModel = $resourceModel;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();

            //if the order exist
            if (isset($order)) {
                $customerId = $order->getCustomerId();
                //if the order is placed by a logged-in user
                if ($order->getCustomerIsGuest() == 0 && isset($customerId)) {

                    //Get Sku associated with store credit
                    $skuStoreCredit = $this->getBundleConfig($order->getStoreId());

                    //Update store credit based on the order item
                    $this->updateStoreCreditByOrder($customerId, $order, $skuStoreCredit);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
    }


    /**
     * @param $storeId
     * @return array
     */
    public function getBundleConfig($storeId)
    {
        $bundleConfigData = $this->scopeConfig->getValue(
            self::ANNUAL_BUNDLE_CONFIG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $skuStoreCredit = [];
        if (is_string($bundleConfigData)) {
            $bundleConfigData = json_decode($bundleConfigData, true);
            foreach ($bundleConfigData as $bundle) {
                if (isset($bundle['sku']) && isset($bundle['store_credit'])) {
                    $skuStoreCredit[$bundle['sku']] = $bundle['store_credit'];
                }
            }
        }

        return $skuStoreCredit;
    }


    /**
     * @param $customerId
     * @param $creditAmount
     * @param $order
     * @return void
     */
    public function addStoreCreditToCustomer($customerId, $creditAmount, $order)
    {
        try {
            if (!$this->customerBalanceData->isEnabled()) {
                return;
            }

            //Get Store Id, Website Id and Customer
            $storeId = $order->getStoreId();
            $websiteId = $order->getStore()->getWebsiteId();
            $customer = $this->customerRepository->getById($customerId);

            $balance = $this->balanceFactory->create();
            $balance->setCustomer($customer)
                ->setCustomerId($customerId)
                ->setWebsiteId($websiteId)
                ->setAmountDelta((float)$creditAmount)
                ->setComment("Credited balance for placing an order #" . $order->getIncrementId())
                ->setNotifyByEmail($this->getIsNotify($storeId), $storeId);
            $this->resourceModel->save($balance);

        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function getIsNotify($storeId)
    {
        return $this->scopeConfig->isSetFlag(
            self::IS_NOTIFY_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

    }

    /**
     * @param $customerId
     * @param $order
     * @param $skuStoreCredit
     * @return void
     */
    public function updateStoreCreditByOrder($customerId, $order, $skuStoreCredit)
    {
        //Get All Order Items
        $orderItems = $order->getAllItems();
        $totalStoreCredit = 0;

        foreach ($orderItems as $orderItem) {

            //check whether if cart contains annual bundle product
            $isAnnualBundle = $orderItem->getProduct()->getData('is_annual_bundle') == 1;
            $qty = $orderItem->getQtyOrdered();

            //Check whether item exist in the config with store credit
            if ($isAnnualBundle && isset($skuStoreCredit[$orderItem->getSku()])) {
                $totalStoreCredit += $skuStoreCredit[$orderItem->getSku()] * $qty;
            }
        }

        //Check store credit exist and store credit !=0
        if (isset($totalStoreCredit) && $totalStoreCredit != 0) {
            // add store credit to customer
            $this->addStoreCreditToCustomer($customerId, $totalStoreCredit, $order);
        }

    }
}
