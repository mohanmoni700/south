<?php

declare (strict_types=1);

namespace Alfakher\StoreCredit\Model\Total\Quote;

use Magento\Checkout\Model\Session;
use Magento\CustomerBalance\Helper\Data;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Store\Model\StoreManagerInterface;

class Customerbalance extends \Magento\CustomerBalance\Model\Total\Quote\Customerbalance
{
    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Customerbalance constructor
     *
     * @param Session $checkoutSession
     * @param Data $customerBalanceData
     * @param BalanceFactory $balanceFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session                $checkoutSession,
        Data                   $customerBalanceData,
        BalanceFactory         $balanceFactory,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface  $storeManager
    )
    {
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $storeManager,
            $balanceFactory,
            $customerBalanceData,
            $priceCurrency,
        );
    }

    /**
     * @inheritDoc
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    )
    {
        if (!$this->_customerBalanceData->isEnabled()) {
            return $this;
        }

        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == Address::TYPE_SHIPPING
            && $quote->isVirtual()
        ) {
            return $this;
        }

        $baseTotalUsed = $totalUsed = $baseUsed = $used =
        $baseStoreCreditAmount = $storeCreditAmount = $baseBalance = $balance = 0;

        $baseStoreCreditAmount = $quote->getStorecreditPartialAmount();
        $baseStoreCreditType = $quote->getStorecreditType();
        $storeCreditAmount = $this->priceCurrency->convert($baseStoreCreditAmount, $quote->getStore());

        if ($quote->getCustomer()->getId()) {
            if ($quote->getUseCustomerBalance()) {
                $store = $this->_storeManager->getStore($quote->getStoreId());
                $baseBalance = $this->_balanceFactory->create()->setCustomer(
                    $quote->getCustomer()
                )->setCustomerId(
                    $quote->getCustomer()->getId()
                )->setWebsiteId(
                    $store->getWebsiteId()
                )->loadByCustomer()->getAmount();
                $balance = $this->priceCurrency->convert($baseBalance, $quote->getStore());
            }
        }

        // Changes for quote calculation after partial store credit
        // Check store credit amount and credit type is partial
        if ($quote->getUseCustomerBalance() && (!empty($baseStoreCreditAmount) &&
                ($baseStoreCreditType == 'partial') && ($baseStoreCreditAmount <= $baseBalance))) {
            if ($baseStoreCreditAmount >= $total->getBaseGrandTotal()) {
                $baseUsed = $total->getBaseGrandTotal();
                $used = $total->getGrandTotal();

                $total->setBaseGrandTotal(0);
                $total->setGrandTotal(0);
            } else {
                $baseUsed = $baseStoreCreditAmount;
                $used = $storeCreditAmount;

                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseStoreCreditAmount);
                $total->setGrandTotal($total->getGrandTotal() - $storeCreditAmount);
            }

        } else {
            $baseAmountLeft = $baseBalance - $quote->getBaseCustomerBalAmountUsed();
            $amountLeft = $balance - $quote->getCustomerBalanceAmountUsed();

            if ($baseAmountLeft >= $total->getBaseGrandTotal()) {
                $baseUsed = $total->getBaseGrandTotal();
                $used = $total->getGrandTotal();

                $total->setBaseGrandTotal(0);
                $total->setGrandTotal(0);
            } else {
                $baseUsed = $baseAmountLeft;
                $used = $amountLeft;

                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseAmountLeft);
                $total->setGrandTotal($total->getGrandTotal() - $amountLeft);
            }
        }
        /* Changes for quote calculation after partial store credit */

        $baseTotalUsed = $quote->getBaseCustomerBalAmountUsed() + $baseUsed;
        $totalUsed = $quote->getCustomerBalanceAmountUsed() + $used;

        $quote->setBaseCustomerBalAmountUsed($baseTotalUsed);
        $quote->setCustomerBalanceAmountUsed($totalUsed);

        $total->setBaseCustomerBalanceAmount($baseUsed);
        $total->setCustomerBalanceAmount($used);
        return $this;
    }
}
