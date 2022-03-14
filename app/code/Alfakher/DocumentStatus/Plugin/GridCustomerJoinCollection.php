<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Alfakher\DocumentStatus\Plugin;

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

        if ($requestName == 'customer_listing_data_source') {
            if (isset($filters['docuement_status'])) {
                $docuemntcollection = $this->collectionDoc->create();
                $documentFilter = $filters['docuement_status'];
                if ($docuemntcollection->getSize() != null) {
                    $pendingDocument = [];
                    $rejectedDocument = [];
                    $approveDocument = [];
                    $expireDocument = [];
                    foreach ($docuemntcollection as $key) {
                        $todate = date("Y-m-d");
                        $expiry_date = $key->getExpiryDate();
                        if ($key->getStatus() == 0 && $key->getMessage() == null) {
                            array_push($pendingDocument, $key['customer_id']);
                        } else {
                            if (($expiry_date <= $todate && $expiry_date != "")) {
                                array_push($expireDocument, $key['customer_id']);
                            } elseif ($key->getStatus() == 0 && $key->getMessage() != null) {
                                array_push($rejectedDocument, $key['customer_id']);
                            } elseif ($key->getStatus() == 1 && $key->getMessage() == null) {
                                array_push($approveDocument, $key['customer_id']);
                            }
                        }
                    }
                    if ($documentFilter == "approve") {
                        $collection->addFieldToFilter("entity_id", ['in' => $approveDocument]);
                    }
                    if ($documentFilter == "rejected") {
                        $collection->addFieldToFilter("entity_id", ['in' => $rejectedDocument]);
                    }
                    if ($documentFilter == "pending") {
                        $collection->addFieldToFilter("entity_id", ['in' => $pendingDocument]);
                    }
                    if ($documentFilter == "expire") {
                        $collection->addFieldToFilter("entity_id", ['in' => $expireDocument]);
                    }
                }
            } else {
                return $collection;
            }

        }
        return $collection;
    }
}
