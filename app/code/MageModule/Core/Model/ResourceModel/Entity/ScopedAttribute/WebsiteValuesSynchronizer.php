<?php

namespace MageModule\Core\Model\ResourceModel\Entity\ScopedAttribute;

use MageModule\Core\Helper\Store as Helper;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WebsiteValueSynchronizer
 *
 * @package MageModule\Core\Model\Entity\ScopedAttribute
 */
class WebsiteValuesSynchronizer
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * WebsiteValuesSynchronizer constructor.
     *
     * @param Helper                $helper
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection    $resourceConnection
     */
    public function __construct(
        Helper $helper,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection
    ) {
        $this->helper             = $helper;
        $this->storeManager       = $storeManager;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Makes sure that all EAV values stay in synch with each other for website-scope attributes
     *
     * @param ScopedAttributeInterface $attribute
     * @param int|string               $websiteId
     *
     * @return $this
     * @throws \Exception
     */
    public function synchronize(ScopedAttributeInterface $attribute, $websiteId)
    {
        if ($attribute->isScopeWebsite() && $attribute instanceof AbstractAttribute) {
            $connection = $this->resourceConnection->getConnection();

            $storeIds = $this->helper->getStoreIdsByWebsiteId($websiteId);
            if ($storeIds) {
                $table  = $attribute->getBackendTable();
                $select = $connection->select()->from($table);
                $select->where('store_id IN(?)', $storeIds);
                $select->where($attribute->getIdFieldName() . ' =?', $attribute->getId());
                $select->group('entity_id');
                $result = $connection->fetchAll($select);

                try {
                    $connection->beginTransaction();
                    foreach ($result as $row) {
                        if (isset($row['value_id'])) {
                            unset($row['value_id']);
                        }

                        if (isset($row['store_id'])) {
                            unset($row['store_id']);
                        }

                        foreach ($storeIds as $storeId) {
                            $updateRow             = $row;
                            $updateRow['store_id'] = $storeId;
                            $connection->insertOnDuplicate($table, $updateRow, ['value']);
                        }
                    }
                    $connection->commit();
                } catch (\Exception $e) {
                    $connection->rollBack();
                    throw $e;
                }
            }
        }

        return $this;
    }
}
