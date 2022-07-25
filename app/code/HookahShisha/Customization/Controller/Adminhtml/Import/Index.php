<?php
namespace HookahShisha\Customization\Controller\Adminhtml\Import;

class Index
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
    }

    /**
     * Index action
     *
     * @param \Magento\ImportExport\Controller\Adminhtml\Import\Index $subject
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function beforeExecute(\Magento\ImportExport\Controller\Adminhtml\Import\Index $subject)
    {
        $this->messageManager->addWarning(
            __("Please see to it that the email addresses are validated or they may cause the customer grid to break!")
        );
    }
}
