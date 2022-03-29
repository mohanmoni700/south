<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Alfakher\MigrationDocument\Plugin;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;

/**
 * Class CollectionPool
 */
class GridCustomerJoinCollection
{
    /**
     *
     * @var table
     */
    public static $table = 'customer_grid_flat';
    /**
     *
     * @var leftJoinTable
     */
    public static $leftJoinTable = 'alfakher_mydocument_mydocument'; // My custom table
    /**
     * GridCustomerJoinCollection
     *
     * @param CollectionFactory $collectionDoc
     * @param Http              $request
     */
    public function __construct(
        CollectionFactory $collectionDoc,
        Http $request
    ) {
        $this->collectionDoc = $collectionDoc;
        $this->request = $request;
    }

    /**
     * Get Report
     *
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param array $collection
     * @param string $requestName
     * @return array
     */
    public function afterGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        $collection,
        $requestName
    ) {
        $filters = $this->request->getParam("filters");

        if (isset($filters['is_migration_documents'])) {
            $migrateDocumentValue = $filters['is_migration_documents'];
            $newUpdatedValue = (int) $migrateDocumentValue;
            $collection->addFieldToFilter("is_migration_documents", $newUpdatedValue);
            return $collection;
        } else {
            return $collection;
        }
        return $collection;
    }
}
