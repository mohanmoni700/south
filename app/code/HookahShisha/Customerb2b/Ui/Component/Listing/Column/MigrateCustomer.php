<?php
namespace HookahShisha\Customerb2b\Ui\Component\Listing\Column;

use \Magento\Customer\Model\CustomerFactory;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Ui\Component\Listing\Columns\Column;

class MigrateCustomer extends Column
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomerFactory $customerFactory
     * @param array $components
     * @param array $data
     **/
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerFactory $customerFactory,
        array $components = [],
        array $data = []
    ) {
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * PrepareDataSource
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $collection = $this->customerFactory->create()->getCollection()
                    ->addAttributeToSelect("*")
                    ->addAttributeToFilter("email", $item["company_email"]);
                $data = $collection->getFirstItem();
                if ($data->getMigrateCustomer() == 1) {
                    $item['migrate_customer'] = 'Yes';
                } elseif ($data->getMigrateCustomer() == 0) {
                    $item['migrate_customer'] = 'No';
                }
            }
        }
        return $dataSource;
    }
}
