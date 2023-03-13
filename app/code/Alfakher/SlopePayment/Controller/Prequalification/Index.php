<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Controller\Prequalification;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action\Action
{
    /**
     * Result Page
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute method
     *
     * @return PageFactory
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Slope Pre-Qualification'));
        return $resultPage;
    }
}
