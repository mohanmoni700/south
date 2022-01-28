<?php
/**
 * Copyright (c) 2019 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2019 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\OrderImportExport\Model\Processor;

use MageModule\OrderImportExport\Api\ImporterInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class Invoice
 *
 * @package MageModule\OrderImportExport\Model\Processor
 */
class Invoice extends \MageModule\OrderImportExport\Model\Processor\AbstractProcessor implements
    \MageModule\OrderImportExport\Model\Processor\ProcessorInterface
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $service;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    private $transactionFactory;

    /**
     * Invoice constructor.
     *
     * @param \Magento\Sales\Api\InvoiceManagementInterface $service
     * @param \Magento\Framework\DB\TransactionFactory      $transactionFactory
     * @param array                                         $excludedFields
     */
    public function __construct(
        \Magento\Sales\Api\InvoiceManagementInterface $service,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->service            = $service;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @param array                                                             $data
     * @param \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order
     *
     * @return $this|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(array $data, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        if ($this->getConfig()->getCreateInvoice() && !$order->getInvoiceCollection()->getSize()) {
            $this->removeExcludedFields($data);

            $qtys = [];

            $products = $data[ImporterInterface::KEY_PRODUCTS_ORDERED];

            /** @var \Magento\Sales\Api\Data\OrderItemInterface|\Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllItems() as $item) {
                $key       = $item->getData('key');
                $parentKey = $item->getData('parent_key');

                if ($parentKey && isset($products[$parentKey]['bundle_items'][$key][OrderItemInterface::QTY_INVOICED])) {
                    $qty = $products[$parentKey]['bundle_items'][$key][OrderItemInterface::QTY_INVOICED];
                } elseif (isset($products[$key][OrderItemInterface::QTY_INVOICED]) && is_numeric($products[$key][OrderItemInterface::QTY_INVOICED])) {
                    $qty = $products[$key][OrderItemInterface::QTY_INVOICED];
                } else {
                    $qty = $item->getQtyToInvoice();
                }

                $qty = (float)$qty;
                if (!$qty) {
                    continue;
                }

                $qtys[$item->getId()] = $qty;
            }

            if ($qtys) {
                /** @var \Magento\Sales\Model\Order\Invoice $invoice */
                $invoice = $this->service->prepareInvoice($order, $qtys);
                $invoice->setCreatedAt($order->getCreatedAt());
                $invoice->setUpdatedAt($order->getCreatedAt());
                $invoice->setDiscountDescription($order->getDiscountDescription());
                $invoice->setDiscountAmount($order->getDiscountAmount());
                $invoice->setBaseDiscountAmount($order->getBaseDiscountAmount());
                $invoice->setDiscountTaxCompensationAmount($order->getDiscountTaxCompensationAmount());
                $invoice->setBaseDiscountTaxCompensationAmount($order->getBaseDiscountTaxCompensationAmount());
                $invoice->setShippingDiscountTaxCompensationAmount($order->getShippingDiscountTaxCompensationAmount());
                $invoice->setBaseShippingDiscountTaxCompensationAmnt($order->getBaseShippingDiscountTaxCompensationAmnt());
                if (!$invoice->getBaseToGlobalRate()) {
                    $invoice->setBaseToGlobalRate($order->getBaseToGlobalRate());
                }

                if ($invoice->getItems()) {
                    if (round($invoice->getGrandTotal(), 4) === round($order->getTotalPaid(), 4)) {
                        $invoice->setState($invoice::STATE_PAID);
                    }

                    $payment       = $order->getPayment();
                    $transactionId = $payment->getCcTransId();
                    if (!$transactionId) {
                        $transactionId = $payment->getLastTransId();
                    }

                    $invoice->setTransactionId($transactionId);
                    $invoice->setSendEmail(false);
                    $invoice->register();
                    $invoice->pay();

                    /** @var \Magento\Framework\DB\Transaction $transaction */
                    $transaction = $this->transactionFactory->create();
                    $transaction->addObject($invoice);
                    $transaction->addObject($order);
                    $transaction->save();
                }

                /** set this here for possible use in order classes */
                $order->setInvoice($invoice);
            }
        }

        return $this;
    }
}
