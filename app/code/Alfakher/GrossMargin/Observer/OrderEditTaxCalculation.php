<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Observer;

use Magento\Tax\Model\Config;
use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use Magento\Framework\Event\Observer;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class OrderEditTaxCalculation implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ExciseTaxConfig
     */
    protected $exciseTaxConfig;

    /**
     * @var Config
     */
    protected $taxConfig;

    /**
     * @var Session
     */
    protected $adminSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    
    /**
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param Config $taxConfig
     * @param Session $adminSession
     * @param OrderStatusHistoryRepositoryInterface $orderStatusRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        ExciseTaxConfig $exciseTaxConfig,
        Config $taxConfig,
        Session $adminSession,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->taxConfig  = $taxConfig;
        $this->adminSession = $adminSession;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        try {
            $order = $observer->getEvent()->getOrder();
            $quote = $observer->getEvent()->getQuote();

            $itemDetailSession = $this->adminSession->getData('tax_data');
            
            if ($quote && $quote->getId()) {

                $shippingAddress = $quote->getShippingAddress();
                $storeId = $quote->getStoreId();
                $isAddressTaxable = $this->exciseTaxConfig->isAddressTaxable($shippingAddress, $storeId);

                if ($isAddressTaxable) {
                    $order->setExciseTaxResponseOrder($quote->getExciseTaxResponseOrder());
                    if (!$quote->getIsMultiShipping()) {
                        if (!is_null($quote->getExciseTax()) && $quote->getExciseTax() > 0) {
                            $order->setExciseTax($quote->getExciseTax());
                        }
                        if (!is_null($quote->getSalesTax()) && $quote->getSalesTax() > 0) {
                            $order->setSalesTax($quote->getSalesTax());
                        }
                        if (!is_null($quote->getExciseTax()) &&
                            $quote->getExciseTax() > 0 || !is_null($quote->getSalesTax()) &&
                            $quote->getSalesTax() > 0) {
                            $order->setTaxAmount($quote->getSalesTax()
                                + $quote->getExciseTax()
                                + $order->getShippingTaxAmount());
                            $order->setBaseTaxAmount($quote->getSalesTax()
                                + $quote->getExciseTax()
                                + $order->getBaseShippingTaxAmount());
                        }
                    } else {
                        $taxSummary = $this->getTaxSummary($order);
                        $order->setExciseTax($taxSummary[1]);
                        $order->setSalesTax($taxSummary[0]);
                    }
                    
                    foreach ($order->getAllItems() as $item) {
                        $quoteItemId = $item->getQuoteItemId();
                        $quoteItem = $quote->getItemById($quoteItemId);

                        if ($quoteItem) {
                            if (!is_null($quote->getSalesTax()) && $quote->getSalesTax() > 0) {
                                $item->setSalesTax($quoteItem->getSalesTax());
                            }
                            if (!is_null($quote->getExciseTax()) && $quote->getExciseTax() > 0) {
                                $item->setExciseTax($quoteItem->getExciseTax());
                            }
                            if (!is_null($quote->getExciseTax()) &&
                                $quote->getExciseTax() > 0 ||
                                !is_null($quote->getSalesTax()) &&
                                $quote->getSalesTax() > 0) {
                                $item->setTaxAmount($quoteItem->getSalesTax() + $quoteItem->getExciseTax());
                            }

                            /* bv_mp; date : 06-09-22; resolving issue of grand total shipping edit; Start */
                            $item->setBaseTaxAmount($quoteItem->getBaseTaxAmount());
                            /* bv_mp; date : 06-09-22; resolving issue of grand total shipping edit; End */

                            if (!is_null($quote->getTaxPercent()) && $quoteItem->getTaxPercent() > 0) {
                                $item->setTaxPercent($quoteItem->getTaxPercent());
                            }

                            /* bv_op; date : 24-8-22; resolving issue of row subtotal; Start */
                            $item->setPriceInclTax($quoteItem->getPriceInclTax());
                            $item->setBasePriceInclTax($quoteItem->getBasePriceInclTax());

                            $item->setRowTotalInclTax($quoteItem->getRowTotal()
                                + $quoteItem->getSalesTax()
                                + $quoteItem->getExciseTax());
                            $item->setBaseRowTotalInclTax($quoteItem->getBaseRowTotal()
                                + $quoteItem->getSalesTax()
                                + $quoteItem->getExciseTax());
                            /* bv_op; date : 24-8-22; resolving issue of row subtotal; End */
                        }
                    }
                } else {
                    $this->clearItemTax($order);
                }
                $this->calculateGrandTotal($order);
                $order->save();
                $this->updateCommentHistory($itemDetailSession, $order->getId());
                $this->adminSession->unsetData('text_data');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Get Tax amounts
     *
     * @param mixed $order
     * @return array
     */
    private function getTaxSummary($order)
    {
        $salesTax = $exciseTax = 0;
        foreach ($order->getAllItems() as $item) {
            $salesTax += $item->getSalesTax();
            $exciseTax += $item->getExciseTax();
        }
        return [$salesTax, $exciseTax];
    }

    /**
     * Clear Excise Tax amounts
     *
     * @param mixed $order
     * @return void
     */
    private function clearItemTax($order)
    {
        foreach ($order->getAllItems() as $item) {
            $item->setSalesTax(0);
            $item->setExciseTax(0);
        }
    }
    
    /**
     * Calculate order GrandTotal
     *
     * @param mixed $order
     * @return void
     */
    protected function calculateGrandTotal($order)
    {
        if ($this->checkTaxConfiguration()) {
            $grandTotal     = $order->getSubtotal()
                + $order->getTaxAmount()
                + $order->getShippingAmount()
                + $order->calculateMageWorxFeeAmount()
                - abs($order->getDiscountAmount())
                - abs($order->getGiftCardsAmount())
                - abs($order->getCustomerBalanceAmount());
            $baseGrandTotal = $order->getBaseSubtotal()
                + $order->getBaseTaxAmount()
                + $order->getBaseShippingAmount()
                + $order->calculateMageWorxBaseFeeAmount()
                - abs($order->getBaseDiscountAmount())
                - abs($order->getBaseGiftCardsAmount())
                - abs($order->getBaseCustomerBalanceAmount());
        } else {
            $grandTotal     = $order->getSubtotalInclTax()
                + $order->getShippingInclTax()
                + $order->calculateMageWorxFeeAmount()
                - abs($order->getDiscountAmount())
                - abs($order->getGiftCardsAmount())
                - abs($order->getCustomerBalanceAmount());
            $baseGrandTotal = $order->getBaseSubtotalInclTax()
                + $order->getBaseShippingInclTax()
                + $order->calculateMageWorxBaseFeeAmount()
                - abs($order->getBaseDiscountAmount())
                - abs($order->getBaseGiftCardsAmount())
                - abs($order->getBaseCustomerBalanceAmount());
        }

        /* bv_op; date : 1-8-22; resolving issue of incorrect subtotal on price change; Start */
        $order->setSubtotalInclTax($order->getSubtotal() + $order->getExciseTax() + $order->getSalesTax());
        /* bv_op; date : 1-8-22; resolving issue of incorrect subtotal on price change; End */

        $order->setGrandTotal($grandTotal)
             ->setBaseGrandTotal($baseGrandTotal)->save();
    }

    /**
     * Check Tax Configuration
     *
     * @return bool
     */
    public function checkTaxConfiguration(): bool
    {
        $catalogPrices         = $this->taxConfig->priceIncludesTax() ? 1 : 0;
        $shippingPrices        = $this->taxConfig->shippingPriceIncludesTax() ? 1 : 0;
        $applyTaxAfterDiscount = $this->taxConfig->applyTaxAfterDiscount() ? 1 : 0;

        return !$catalogPrices && !$shippingPrices && $applyTaxAfterDiscount;
    }

    /**
     * Update comment history
     *
     * @param array $itemData
     * @param int $orderId
     * @return OrderStatusHistoryInterface|null
     */
    public function updateCommentHistory($itemData, $orderId)
    {
        $order = null;
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            $this->logger->error($exception->getMessage());
        }

        $orderHistory = null;
        
        $changes = [];

        foreach ($itemData as $key => $item) {
            $itemData = $this->orderItemRepository->get($key);

            // tax amount
            if (isset($item['paramTaxAmount'])) {
                $taxAmount = $itemData->getTaxAmount();
                $origTaxAmount = $item['oldtax'];

                if ($origTaxAmount != $taxAmount) {
                    $changes[] = 'Changes for item <b>'.$itemData->getName().'</b><br>';
                    $changes[] = 'Tax Amount has been changed from <b>'.
                    $order->formatPriceTxt($origTaxAmount).'</b> to <b>'.$order->formatPriceTxt($taxAmount).'</b><br>';
                }
            }

            // tax percent
            if (isset($item['paramTaxPer'])) {
                $origValue = $item['oldTaxPer'];

                if ($origValue != $item['paramTaxPer']) {
                    $changes[] = 'Tax Percent has been changed from <b>'.
                    round($origValue, 2).'</b> to <b>'.round($item['paramTaxPer'], 2).'</b><br>';
                }
            }

            // price
            if (isset($item['paramPrice'])) {
                $price = $item['paramPrice'];
                $origPrice = $item['oldPrice'];

                if ($origPrice != $price) {
                    $changes[] = 'Price has been changed from <b>'.
                    $order->formatPriceTxt($origPrice).'</b> to <b>'.$order->formatPriceTxt($price).'</b><br>';
                }
            }

            // discount amount
            if (isset($item['paramDiscountAmt'])) {
                $discountAmount = $item['paramDiscountAmt'];
                $origDiscountAmount = $item['oldDiscount'];

                if ($origDiscountAmount != $discountAmount) {
                    $changes[] = 'Discount has been changed from <b>'.
                    $order->formatPriceTxt($origDiscountAmount).'</b> to <b>'.
                    $order->formatPriceTxt($discountAmount).'</b>';
                }
            }

        }

        $commString = implode(" ", $changes);
        if ($order) {
            $comment = $order->addCommentToStatusHistory($commString);
            try {
                $orderHistory = $this->orderStatusRepository->save($comment);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $orderHistory;
    }
}
