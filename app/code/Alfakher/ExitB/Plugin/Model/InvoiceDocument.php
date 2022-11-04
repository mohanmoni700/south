<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Plugin\Model;

use Magento\Sales\Model\Order\InvoiceDocumentFactory as Subject;
use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\OrderInterface;

class InvoiceDocument
{

    /**
     * Set Exitb Number in invoice
     *
     * @param InvoiceDocumentFactory $subject
     * @param mixed $result
     * @param OrderInterface $order
     * @param array $items
     * @param InvoiceCommentCreationInterface|null $comment
     * @param bool|false $appendComment
     * @param InvoiceCreationArgumentsInterface|null $arguments
     * @return InvoiceInterface $result
     */
    public function afterCreate(
        Subject $subject,
        $result,
        OrderInterface $order,
        $items = [],
        InvoiceCommentCreationInterface $comment = null,
        $appendComment = false,
        InvoiceCreationArgumentsInterface $arguments = null
    ) {
        if (!empty($arguments)) {
            $extensionAttributes = $arguments->getExtensionAttributes();
            $result->setData('exitb_invoice_numbers', $extensionAttributes->getExitbInvoiceNumbers());
        }
        return $result;
    }
}
