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
namespace Webkul\MultiQuickbooksConnect\Controller\Adminhtml\Ordermap;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Webkul\MultiQuickbooksConnect\Api\OrderMapRepositoryInterface;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var OrderMapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        OrderMapRepositoryInterface $orderMapRepository
    ) {
        $this->orderMapRepository = $orderMapRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $collection = $this->orderMapRepository->getCollectionByIds($params['orderEntityIds']);
            $orderRecordDeleted = 0;
            foreach ($collection->getItems() as $orderMap) {
                $orderMap->setId($orderMap->getEntityId());
                $this->deleteMappedOrderRecord($orderMap);
                ++$orderRecordDeleted;
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $orderRecordDeleted));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(
            '*/account/edit',
            ['id' => $params['account_id'], 'active_tab' => 'maporder']
        );
    }

    /**
     * deleteMappedOrderRecord
     * @param Webkul\MultiQuickbooksConnect\Model\OrderMap $orderMap
     * @return void
     */
    private function deleteMappedOrderRecord($orderMap)
    {
        $orderMap->delete();
    }

    /**
     * Check Order Map recode delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MultiQuickbooksConnect::order_map_delete');
    }
}
