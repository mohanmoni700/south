<?php

namespace Avalara\Excise\Controller\Adminhtml\Log;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Avalara\Excise\Model\Clear as ClearLogs;
use Avalara\Excise\Logger\ExciseLogger;
/**
 * @codeCoverageIgnore
 */
class Clear extends LogAbstract
{
    /**
     * @var ClearLogs
     */
    protected $clearLogs;

    /**
     * @var ExciseLogger
     */
    protected $exciseLogger;

    /**
     * Process constructor
     *
     * @param Context $context
     * @param ClearLogs $clearLogs
     * @param ExciseLogger $exciseLogger
     */
    public function __construct(
        Context $context,
        ClearLogs $clearLogs,
        ExciseLogger $exciseLogger
    ) {
        $this->clearLogs = $clearLogs;
        $this->exciseLogger = $exciseLogger;
        parent::__construct($context);
    }

    /**
     * @return Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Initiate Log Clearing
        try {
            $count = $this->clearLogs->clearDbLogs();

            if ($count > 0) {
                $message = __(
                    '%1 log records were cleared.',
                    $count
                );

                // Display message on the page
                $this->messageManager->addSuccess($message);
            } else {
                // Display message on the page
                $this->messageManager->addSuccess(__('No logs needed to be cleared.'));
            }
        } catch (\Exception $e) {
            // Build error message
            $message = __('An error occurred while clearing the log.');

            // Display error message on the page
            $this->messageManager->addErrorMessage($message . "\n" . __('Error Message: ') . $e->getMessage());

            // Log the exception
            $this->exciseLogger->error(
                $message,
                [ /* context */
                    'exception' => sprintf(
                        'Exception message: %s%sTrace: %s',
                        $e->getMessage(),
                        "\n",
                        $e->getTraceAsString()
                    ),
                ]
            );
            // code to add CEP logs for exception
            try {
                $functionName = __METHOD__;
                $operationName = get_class($this);    
                // @codeCoverageIgnoreStart            
                $this->exciseLogger->logDebugMessage(
                    $functionName,
                    $operationName,
                    $e
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
        }

        // Redirect browser to log list page
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
