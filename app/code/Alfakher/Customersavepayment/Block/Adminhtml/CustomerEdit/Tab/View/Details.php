<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Tab\View;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackenHelper;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;

class Details extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * [__construct]
     *
     * @param \Magento\Vault\Model\CreditCardTokenFactory $collectionFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param BackenHelper $backendHelper
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList
     * @param \ParadoxLabs\TokenBase\Model\CardFactory $cardCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Vault\Model\CreditCardTokenFactory $collectionFactory,
        Registry $coreRegistry,
        Context $context,
        BackenHelper $backendHelper,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        \ParadoxLabs\TokenBase\Model\CardFactory $cardCollectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->customer = $customer;
        $this->paymentMethodList = $paymentMethodList;
        $this->cardCollectionFactory = $cardCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * [_construct]
     *
     * @return mixed
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }
    /**
     * [_prepareCollection]
     *
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customerData = $this->customer->load($customerId);

        $storeId = $customerData->getStoreId();
        $activePaymentMethodList = $this->paymentMethodList->getActiveList($storeId);
        foreach ($activePaymentMethodList as $payment) {
            $paymentMethodCode = $payment->getCode();
            if ($paymentMethodCode == "spreedly") {
                $collection = $this->_collectionFactory->create()
                    ->getCollection()->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('is_visible', 1);
                if (!empty($collection)) {
                    $this->setCollection($collection);
                    return parent::_prepareCollection();
                }
            } elseif ($paymentMethodCode == "paradoxlabs_firstdata") {
                $collection = $this->cardCollectionFactory->create()
                    ->getCollection()->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('active', 1);
                if (!empty($collection)) {
                    $this->setCollection($collection);
                    return parent::_prepareCollection();
                }
            }
        }
    }

    /**
     * [_prepareColumns]
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Id'),
                'index' => 'entity_id',
                'renderer' => 'Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer\EntityId',
            ]
        );

        $this->addColumn(
            'details',
            [
                'header' => __('Details'),
                'index' => 'details',
                'renderer' => 'Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer\Details',
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'delete_action',
                'renderer' => DeactiveAction::class,
                'header_css_class' => 'col-actions',
                'column_css_class' => 'col-actions',
            ]
        );

        return parent::_prepareColumns();
    }
}
