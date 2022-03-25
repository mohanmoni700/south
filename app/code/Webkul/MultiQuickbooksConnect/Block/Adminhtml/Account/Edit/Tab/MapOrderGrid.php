<?php
/**
 * Webkul MultiQuickbooksConnect
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\Account\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Webkul\MultiQuickbooksConnect\Api\OrderMapRepositoryInterface;

class MapOrderGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param OrderMapRepositoryInterface $orderMapRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        OrderMapRepositoryInterface $orderMapRepository,
        array $data = []
    ) {
        $this->orderMapRepository = $orderMapRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quickbooks_map_order');
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
        if ($id) {
            $collection = $this->orderMapRepository->getCollectionByAccountId($id, true);
        } else {
            $collection = $this->orderMapRepository->create();
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Mage Order Id'),
                'sortable' => true,
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'mage_invoice_id',
            [
                'header' => __('Mage Invoice Id'),
                'sortable' => false,
                'index' => 'mage_invoice_id'
            ]
        );
        $this->addColumn(
            'quickbook_sales_doc_number',
            [
                'header' => __('Quickbooks Sales Receipt Doc Number'),
                'sortable' => false,
                'index' => 'quickbook_sales_doc_number'
            ]
        );
        // $this->addColumn(
        //     'customer_email',
        //     [
        //         'header' => __('Customer E-mail'),
        //         'sortable' => false,
        //         'index' => 'customer_email'
        //     ]
        // );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'sortable' => false
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
                'header' => __('Purchase Date'),
                'sortable' => false,
                'index' => 'created_at',
                'type' => 'datetime'
            ]
        );
        $this->addColumn(
            'mapped_on',
            [
                'header' => __('Export To Quickbook On'),
                'sortable' => false,
                'index' => 'mapped_on',
                'type' => 'datetime'
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getMageOrderId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => ['base' => 'sales/order/view'],
                        'field' => 'order_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'mage_order_id',
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
            'delete',
            [
                'label' => __('Delete Order Map Record'),
                'url' => $this->getUrl('multiquickbooksconnect/*/MassDelete', ['account_id' => $accountId]),
                'confirm' => __('Are you sure want to delete?')
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('multiquickbooksconnect/ordermap/resetindexgrid', ['_current' => true]);
    }
}
