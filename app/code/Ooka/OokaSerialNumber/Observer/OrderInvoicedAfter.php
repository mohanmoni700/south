<?php

declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;

class OrderInvoicedAfter implements ObserverInterface
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private InvoiceRepositoryInterface $invoiceRepository;
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute method to set product attribute in invoicelevel
     *
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Invoice $invoice
         */
        $invoice = $observer->getEvent()->getInvoice();

        if (!empty($invoice) && !empty($invoice->getItems())) {
            $invoiceItems = $invoice->getItems();
            $updatedInvoiceItems = [];
            foreach ($invoiceItems as $item) {
                $product = $this->productRepository->get($item->getSku(), false, $invoice->getStoreId());
                $item->setData("is_serialize", $product->getData("ooka_require_serial_number"));
                $updatedInvoiceItems [] = $item;
            }
            $invoice->setItems($updatedInvoiceItems);
            $this->invoiceRepository->save($invoice);
        }
    }
}
