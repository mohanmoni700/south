<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Tab\View;

use Corra\Spreedly\Model\Ui\ConfigProvider as CorraConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackenHelper;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Customer;
use Magento\Framework\Registry;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use ParadoxLabs\FirstData\Model\ConfigProvider as ParadoxsConfig;
use ParadoxLabs\TokenBase\Model\CardFactory;

class Details extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * [__construct]
     *
     * @param CreditCardTokenFactory $collectionFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param BackenHelper $backendHelper
     * @param Customer $customer
     * @param PaymentMethodListInterface $paymentMethodList
     * @param CardFactory $cardCollectionFactory
     * @param array $data
     */
    public function __construct(
        CreditCardTokenFactory $collectionFactory,
        Registry $coreRegistry,
        Context $context,
        BackenHelper $backendHelper,
        Customer $customer,
        PaymentMethodListInterface $paymentMethodList,
        CardFactory $cardCollectionFactory,
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
            if ($paymentMethodCode === CorraConfig::CODE) {
                $collection = $this->_collectionFactory->create()
                    ->getCollection()->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('is_visible', 1);
                if (!empty($collection)) {
                    $this->setCollection($collection);
                    return parent::_prepareCollection();
                }
            } elseif ($paymentMethodCode === ParadoxsConfig::CODE) {
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
                'renderer' => \Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer\EntityId::class,
            ]
        );

        $this->addColumn(
            'details',
            [
                'header' => __('Details'),
                'index' => 'details',
                'renderer' => \Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer\Details::class,
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
