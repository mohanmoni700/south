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
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml\CreditmemoMap;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Webkul\MultiQuickbooksConnect\Api\CreditmemoMapRepositoryInterface;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var CreditmemoMapRepositoryInterface
     */
    private $creditmemoMapRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        CreditmemoMapRepositoryInterface $creditmemoMapRepository
    ) {
        $this->creditmemoMapRepository = $creditmemoMapRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $collection = $this->creditmemoMapRepository->getCollectionByIds($params['creditmemoEntityIds']);
            $creditmemoRecordDeleted = 0;
            foreach ($collection->getItems() as $creditmemoMap) {
                $creditmemoMap->setId($creditmemoMap->getEntityId());
                $this->deleteMappedCreditmemoRecord($creditmemoMap);
                ++$creditmemoRecordDeleted;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $creditmemoRecordDeleted)
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(
            '*/account/edit',
            ['id' => $params['account_id'], 'active_tab' => 'mapcreditmemo']
        );
    }

    /**
     * deleteMappedCreditmemoRecord
     * @param Webkul\MultiQuickbooksConnect\Model\CreditmemoMap $creditmemoMap
     * @return void
     */
    private function deleteMappedCreditmemoRecord($creditmemoMap)
    {
        $creditmemoMap->delete();
    }

    /**
     * Check Creditmemo Map recode delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MultiQuickbooksConnect::creditmemo_map_delete');
    }
}
