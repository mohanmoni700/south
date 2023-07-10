<?php
declare(strict_types=1);

namespace Alfakher\RequestQuote\Plugin\RequestQuote\Controller\Move;

use Amasty\RequestQuote\Controller\Move\InCart as AmastyInCart;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Quote\Api\CartRepositoryInterface as MagentoQuoteRepository;
use Magento\Checkout\Model\Session as CheckoutSession;

class InCart
{
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var MagentoQuoteRepository
     */
    private MagentoQuoteRepository $magentoQuoteRepository;

    /**
     * @param CheckoutSession $checkoutSession
     * @param MagentoQuoteRepository $magentoQuoteRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        MagentoQuoteRepository $magentoQuoteRepository

    ) {
        $this->checkoutSession = $checkoutSession;
        $this->magentoQuoteRepository = $magentoQuoteRepository;
    }

    /**
     * Amasty After move to cart Plugin
     *
     * @param AmastyInCart $subject
     * @param Redirect $result
     * @return Redirect
     */
    public function afterExecute(AmastyInCart $subject, \Magento\Framework\Controller\Result\Redirect $result)
    {

        $qtID = $this->checkoutSession->getQuoteId();
        $currentQuote = $this->magentoQuoteRepository->get($qtID);
        $this->magentoQuoteRepository->save($currentQuote);
        return $result;
    }
}
