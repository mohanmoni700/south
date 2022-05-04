<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MultiQuickbooksConnect
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model;

class CreditmemoMapRepository implements \Webkul\MultiQuickbooksConnect\Api\CreditmemoMapRepositoryInterface
{
    /**
     * @var CreditmemoMapFactory
     */
    private $quickbooksCreditmemo;

    /**
     * @param CreditmemoMapFactory $quickbooksCreditmemo
     */
    public function __construct(
        CreditmemoMapFactory $quickbooksCreditmemo
    ) {
        $this->quickbooksCreditmemo = $quickbooksCreditmemo;
    }

    /**
     * @param  int $id
     * @return object
     */
    public function getById($id)
    {
        $order = $this->quickbooksCreditmemo->create()->load($id);
        return $quickbooksCreditmemo;
    }

    /**
     * @param  int $mageCreditmemoId
     * @return object
     */
    public function getByMageCreditmemoId($mageCreditmemoId)
    {
        $creditmemo = $this->quickbooksCreditmemo->create()->getCollection()
                            ->addFieldToFilter('mage_creditmemeo_id', ['eq' => $mageCreditmemoId])
                            ->setPageSize(1)->getFirstItem();
        return $creditmemo;
    }

    /**
     * get collection by entity ids
     * @param  array $entityIds
     * @return object
     */
    public function getCollectionByIds(array $entityIds)
    {
        $creditmemoCollection = $this->quickbooksCreditmemo->create()->getCollection()
                            ->addFieldToFilter(
                                'entity_id',
                                [
                                    'in' => $entityIds
                                ]
                            );
        return $creditmemoCollection;
    }

    /**
     * Get mapped creditmemo collection by quickbooks account id
     *
     * @param string $accountId
     * @return void
     */
    public function getCollectionByAccountId($accountId, $joinFlag = false)
    {
        $creditmemoCollection = $this->quickbooksCreditmemo->create()->getCollection()
                            ->addFieldToFilter(
                                'account_id',
                                [
                                    'eq' => $accountId
                                ]
                            );
        return $creditmemoCollection;
    }
}
