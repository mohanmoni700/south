<?php

namespace Alfakher\RequestQuote\Plugin\RequestQuote\Controller\Move;

use Amasty\RequestQuote\Controller\Move\InCart as AmastyInCart;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\App\RequestInterface;
class InCart
{
    /** @var QuoteFactory */
    protected QuoteFactory $quoteFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param QuoteFactory $quoteFactory
     * @param RequestInterface $request
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        RequestInterface $request
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->request = $request;
    }

    /**
     * Amasty After move to cart Plugin
     *
     * @param AmastyInCart $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(AmastyInCart $subject, $result)
    {
        $quoteId = (int) $this->request->getParam('quote_id');
        if (!empty($quoteId)) {
            $quote = $this->quoteFactory->create();
            $quote->load($quoteId);
            $quote->collectTotals()->save();
        }
        return $result;
    }
}
