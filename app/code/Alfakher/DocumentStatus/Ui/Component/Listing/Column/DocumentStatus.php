<?php

namespace Alfakher\DocumentStatus\Ui\Component\Listing\Column;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class DocumentStatus extends Column
{
    /**
     *
     * @var customerFactory
     */
    protected $customerFactory;

    /**
     *
     * @var _searchCriteria
     */
    protected $_searchCriteria;
    /**
     * Document Status
     *
     * @param ContextInterface      $context
     * @param UiComponentFactory    $uiComponentFactory
     * @param SearchCriteriaBuilder $criteria
     * @param CollectionFactory     $collection
     * @param array                 $components
     * @param array                 $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $criteria,
        CollectionFactory $collection,
        array $components = [],
        array $data = []
    ) {
        $this->_searchCriteria = $criteria;
        $this->collection = $collection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * PrepareData Source
     *
     * @param  array  $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {
                $customerId = $item['entity_id'];
                $collection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customerId]);
                if ($collection->getSize()) {
                    $pendingDocument = 0;
                    $rejectedDocument = 0;
                    $approveDocument = 0;
                    $expireDocument = 0;
                    foreach ($collection as $key) {
                        $todate = date("Y-m-d");
                        $expiry_date = $key->getExpiryDate();
                        if ($key->getStatus() == 0 && $key->getMessage() == null) {
                            $pendingDocument++;
                        } else {
                            if (($expiry_date <= $todate && $expiry_date != "")) {
                                $expireDocument++;
                            } elseif ($key->getStatus() == 0 && $key->getMessage() != null) {
                                $rejectedDocument++;
                            } elseif ($key->getStatus() == 1 && $key->getMessage() == null) {
                                $approveDocument++;
                            }
                        }
                    }
                    $item[$this->getData('name')] = "Pending Document :" . $pendingDocument .
                        "\n Rejected Document :" . $rejectedDocument .
                        "\n Approved Document : " . $approveDocument .
                        "\n Expired Document : " . $expireDocument;
                } else {
                    $item[$this->getData('name')] = "Document Not Available";
                }

            }
        }
        return $dataSource;
    }
}
