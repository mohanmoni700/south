<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Helper;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper;

/**
 * Helper for extracting information from the payment data structure
 */
class SubjectReader
{
    /**
     * Reads response from subject
     *
     * @param array $subject
     * @return array
     */
    public function readResponse(array $subject): ?array
    {
        return Helper\SubjectReader::readResponse($subject);
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Reads store's ID, otherwise returns null.
     *
     * @param array $subject
     * @return int|null
     */
    public function readStoreId(array $subject)
    {
        return $subject['store_id'] ?? null;
    }
}
