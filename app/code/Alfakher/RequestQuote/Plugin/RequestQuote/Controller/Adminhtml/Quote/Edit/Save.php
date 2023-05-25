<?php
/**
 * @category  Alfakher
 * @package   Alfakher_RequestQuote
 */
declare(strict_types=1);
namespace Alfakher\RequestQuote\Plugin\RequestQuote\Controller\Adminhtml\Quote\Edit;

use Amasty\RequestQuote\Controller\Adminhtml\Quote\Edit\Save as AmastySave;
use Magento\Quote\Model\QuoteFactory;

class Save
{
    /** @var QuoteFactory */
    protected $quoteFactory;

    /**
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Amasty After Save Plugin
     * @param AmastySave $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(
        AmastySave $subject,
        $result
    ) {
        $quoteId = $subject->getRequest()->getParam('quote_id', false);
        if (!empty($quoteId)) {
            $quote = $this->quoteFactory->create()->load($quoteId);
            $quote->collectTotals()->save();
        }
        return $result;
    }
}
