<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml\Account;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MultiQuickbooksConnect\Model\ResourceModel\Account\CollectionFactory;
use Webkul\MultiQuickbooksConnect\Controller\Adminhtml\AbstractAccount as QuickbooksAccount;

class MassDelete extends QuickbooksAccount
{
    /**
     * Massactions filter.
     *
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $accountsRecordDeleted = 0;
            foreach ($collection->getItems() as $accounts) {
                $accounts->setId($accounts->getEntityId());
                $accounts->delete();
                ++$accountsRecordDeleted;
            }
            $this->messageManager->addSuccess(__("A total of %1 record(s) have been deleted.", $accountsRecordDeleted));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
