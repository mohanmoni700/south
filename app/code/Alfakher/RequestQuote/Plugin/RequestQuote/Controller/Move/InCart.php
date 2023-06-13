<?php

namespace Alfakher\RequestQuote\Plugin\RequestQuote\Controller\Move;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface as AmastyQuoteRepository;
use Amasty\RequestQuote\Controller\Move\InCart as AmastyInCart;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Quote\Api\CartRepositoryInterface as MagentoQuoteRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class InCart
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var AmastyQuoteRepository
     */
    private AmastyQuoteRepository $amastyQuoteRepository;
    /**
     * @var MagentoQuoteRepository
     */
    private MagentoQuoteRepository $magentoQuoteRepository;

    /**
     * @param CheckoutSession $checkoutSession
     * @param AmastyQuoteRepository $amastyQuoteRepository
     * @param RequestInterface $request
     * @param MagentoQuoteRepository $magentoQuoteRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        AmastyQuoteRepository $amastyQuoteRepository,
        RequestInterface $request,
        MagentoQuoteRepository $magentoQuoteRepository

    ) {
        $this->request = $request;
        $this->amastyQuoteRepository = $amastyQuoteRepository;
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
        $quoteId = (int) $this->request->getParam('quote_id');
        if ($quoteId) {
            $currentQuote = $this->checkoutSession->getQuote();
            $approvedQuote = $this->amastyQuoteRepository->get($quoteId);
            if ($currentQuote->getSubtotal() == 0 || $currentQuote->getBaseSubtotal() == 0
                || $currentQuote->getGrandTotal() == 0|| $approvedQuote->getBaseGrandTotal() == 0) {
                $currentQuote->setSubtotal($approvedQuote->getSubtotal());
                $currentQuote->setBaseGrandTotal($approvedQuote->getBaseGrandTotal());
                $currentQuote->setGrandTotal($approvedQuote->getGrandTotal());
                $currentQuote->setBaseSubtotal($approvedQuote->getBaseSubtotal());
            }
            $this->magentoQuoteRepository->save($currentQuote);
        }
        return $result;
    }
}
