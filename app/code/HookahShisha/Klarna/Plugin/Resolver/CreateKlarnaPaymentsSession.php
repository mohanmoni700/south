<?php

namespace HookahShisha\Klarna\Plugin\Resolver;

use Exception;
use Klarna\Kp\Model\QuoteFactory;
use Klarna\Kp\Model\QuoteRepository;
use Klarna\KpGraphQl\Model\Resolver\CreateKlarnaPaymentsSession as BaseCreateKlarnaPaymentsSession;
use Psr\Log\LoggerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class CreateKlarnaPaymentsSession
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @var QuoteRepository
     */
    private QuoteRepository $quoteRepository;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;

    /**
     * Constructor
     *
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param QuoteRepository $quoteRepository
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        QuoteRepository $quoteRepository,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger,
        QuoteFactory $quoteFactory
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->quoteRepository = $quoteRepository;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Before resolve plugin
     *
     * @param BaseCreateKlarnaPaymentsSession $subject
     * @param Field $field
     * @param [type] $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array []
     */
    public function beforeResolve(
        BaseCreateKlarnaPaymentsSession $subject, // NOSONAR
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $maskedCartId = $args['input']['cart_id'];
            $resetSession = $args['input']['reset_session'] ?? false;
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
            $this->updateQuotes($cartId, $resetSession);
        } catch (Exception $exception) {
            $this->logger->error('createKlarnaPaymentSession' . $exception->getMessage());
        }

        return [$field, $context, $info, $value, $args];
    }

    /**
     * Get Quotes Collection
     *
     * @param integer $cartId
     * @param boolean $resetSession
     */
    private function updateQuotes(int $cartId, bool $resetSession)
    {
        try {
            $quoteCollection = $this->quoteFactory->create()->getCollection()
                ->addFieldToFilter('quote_id', $cartId)
                ->addFieldToFilter('is_active', 1)
                ->setOrder('payments_quote_id', 'ASC');

            if ($quoteCollection && $quoteCollection->getSize()) {
                $lastItem = $quoteCollection->getLastItem();
                foreach ($quoteCollection as $quote) {
                    if (!$resetSession && $lastItem->getPaymentsQuoteId() === $quote->getPaymentsQuoteId()) {
                        continue;
                    }
                    $quote->setIsActive(0);
                    $quote->save();
                }
            }
        } catch (Exception $exception) {
            $this->logger->error('createKlarnaPaymentSession' . $exception->getMessage());
        }
    }
}
