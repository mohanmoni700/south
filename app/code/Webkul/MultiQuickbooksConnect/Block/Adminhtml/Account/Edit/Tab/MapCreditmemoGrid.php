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
use Webkul\MultiQuickbooksConnect\Api\CreditmemoMapRepositoryInterface;

class MapCreditmemoGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var CreditmemomapRepositoryInterface
     */
    private $creditmemoMapRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param CreditmemoMapRepositoryInterface $creditmemoMapRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CreditmemoMapRepositoryInterface $creditmemoMapRepository,
        array $data = []
    ) {
        $this->creditmemoMapRepository = $creditmemoMapRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quickbooks_map_creditmemo');
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
            $collection = $this->creditmemoMapRepository->getCollectionByAccountId($id, true);
        } else {
            $collection = $this->creditmemoMapRepository->create();
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
            'mage_creditmemo_id',
            [
                'header' => __('Mage Creditmemo Id'),
                'sortable' => true,
                'index' => 'mage_creditmemo_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'quickbook_creditmemo_id',
            [
                'header' => __('Quickbooks Creditmemo Id'),
                'sortable' => false,
                'index' => 'quickbook_creditmemo_id'
            ]
        );
        $this->addColumn(
            'quickbook_creditmemo_doc_number',
            [
                'header' => __('Quickbooks Creditmemo Doc Number'),
                'sortable' => false,
                'index' => 'quickbook_creditmemo_doc_number'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Export To Quickbook On'),
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
                'getter' => 'getMageCreditmemoId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => ['base' => 'sales/creditmemo/view'],
                        'field' => 'creditmemo_id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'mage_creditmemo_id',
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
        $this->getMassactionBlock()->setFormFieldName('creditmemoEntityIds');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete Credit Memo Map Record'),
                'url' => $this->getUrl('multiquickbooksconnect/*/MassDelete', ['account_id' => $accountId]),
                'confirm' => __('Are you sure want to deletee?')
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('multiquickbooksconnect/creditmemomap/resetindexgrid', ['_current' => true]);
    }
}
