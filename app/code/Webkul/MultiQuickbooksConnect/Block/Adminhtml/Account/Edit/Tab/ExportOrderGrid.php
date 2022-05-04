<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MultiQuickbooksConnect
 * @author      Webkul
 * @copyright   Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Webkul\MultiQuickbooksConnect\Api\OrderMapRepositoryInterface;

class ExportOrderGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\Data
     */
    private $helperData;

    /**
     * @var OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Webkul\MultiQuickbooksConnect\Helper\Data $helperData
     * @param OrderMapRepositoryInterface $orderMapRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Webkul\MultiQuickbooksConnect\Helper\Data $helperData,
        OrderMapRepositoryInterface $orderMapRepository,
        array $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->helperData = $helperData;
        $this->orderMapRepository = $orderMapRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quickbooks_export_order');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        $this->setSortable(true);
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setUseSelectAll(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $id =  $this->getRequest()->getParam('id');
        $accountConfig = $this->helperData->getQuickbooksAccountConfig($id);
        $mappedOrderIds = $this->orderMapRepository->getCollectionByAccountId($id)
            ->getColumnValues('mage_order_id');

        $orderCollection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('store_id', $accountConfig['store_id']);

        if (!empty($mappedOrderIds)) {
            $orderCollection->addFieldToFilter('entity_id', ['nin' => $mappedOrderIds]);
        }

        $this->setCollection($orderCollection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Increment Id'),
                'sortable' => false,
                'index' => 'increment_id'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'sortable' => false
            ]
        );
        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer E-mail'),
                'sortable' => false,
                'index' => 'customer_email'
            ]
        );
        // $this->addColumn(
        //     'store_id',
        //     [
        //         'header' => __('Purchase Point'),
        //         'renderer' => \Webkul\MultiQuickbooksConnect\Block\Adminhtml\OrderMap\Grid\Tab\Store::class,
        //         'index' => 'store_id',
        //         "filter" => false,
        //         'sortable' => false
        //     ]
        // );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'sortable' => false,
                'index' => 'created_at',
                'type' => 'datetime'
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => ['base' => 'sales/order/view'],
                        'field' => 'order_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'entity_id',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * get massaction
     * @return object
     */
    protected function _prepareMassaction()
    {
        $accountId = $this->getRequest()->getParam('id');
        $this->setMassactionIdField('entity_id');
        $this->setChild('massaction', $this->getLayout()->createBlock($this->getMassactionBlockName()));
        $this->getMassactionBlock()->setFormFieldName('orderEntityIds');
        $this->getMassactionBlock()->addItem(
            'export',
            [
                'label' => __('Export Orders to quickbooks'),
                'url' => $this->getUrl('multiquickbooksconnect/*/importinquickbooks', ['account_id' => $accountId]),
                'confirm' => __('Are you sure want to export?')
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('multiquickbooksconnect/ordermap/resetexportgrid', ['_current' => true]);
    }
}
