<?php
namespace Alfakher\MyDocument\Ui\Component\Listing\Column;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Documentcheck extends Column
{
    /**
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory  $collection
     * @param array              $components
     * @param array              $data
     */
    protected $_customerRepositoryInterface;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $collection,
        array $components = [],
        array $data = []
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
        $this->collection = $collection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $collection = $this->collection->create()
                    ->addFieldToFilter('customer_id', ['eq' => $item['entity_id']]);
                $customerId = $item['entity_id'];

                $customer = $this->_customerFactory->create()->load($customerId)->getDataModel();
                if ($collection->getdata()) {

                    $item[$this->getData('name')] = '1';
                    $customer->setCustomAttribute('uploaded_doc', 1);
                    $this->_customerRepositoryInterface->save($customer);

                } else {
                    $item[$this->getData('name')] = '0';
                    $customer->setCustomAttribute('uploaded_doc', 0);
                    $this->_customerRepositoryInterface->save($customer);
                }
            }

        }

        return $dataSource;
    }
}
