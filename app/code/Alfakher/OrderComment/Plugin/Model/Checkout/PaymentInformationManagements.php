<?php
namespace Alfakher\OrderComment\Plugin\Model\Checkout;

class PaymentInformationManagements
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     *
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->_filterManager = $filterManager;
        $this->_quoteRepository = $quoteRepository;
    }
    /**
     * BeforeSavePaymentInformation
     *
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function beforeSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        $orderComment = $paymentMethod->getExtensionAttributes();
        if ($orderComment->getComment()) {
            $comment = trim($orderComment->getComment());
        } else {
            $comment = '';
        }
        $quote = $this->_quoteRepository->getActive($cartId);
        $quote->setOrderComment($comment);
    }
}
