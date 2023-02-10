<?php
namespace Infibeam\Ccavenue\Controller\Transparent;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Redirect extends Action implements CsrfAwareActionInterface
{

    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }
    
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    
    /**
     * @var \Infibeam\Ccavenue\Logger\Logger
     */
    protected $_logger;

    /**
     * @var LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * Redirect constructor.
     * @param \Infibeam\Ccavenue\Logger\Logger $__logger
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        \Infibeam\Ccavenue\Logger\Logger $logger,
        LayoutFactory $resultLayoutFactory)
    {
        $this->_logger = $logger;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $gatewayResponse = $this->getRequest()->getPostValue();
        $this->_logger->info('Transparent redirect');

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getUpdate()->load(['ccavenue_transparent_redirect']);

        return $resultLayout;
    }
}