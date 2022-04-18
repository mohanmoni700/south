<?php
namespace Alfakher\SalesApprove\Block\Adminhtml\Order;

/**
 * Adminhtml sales order create
 *
 */
class Create extends \Magento\Sales\Block\Adminhtml\Order\Create
{
    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'order';
        $this->_mode = 'create';

        parent::_construct();

        $this->setId('sales_order_create');

        $customerId = $this->_sessionQuote->getCustomerId();
        $storeId = $this->_sessionQuote->getStoreId();

        $this->buttonList->update('save', 'label', __('Submit Order'));
        $this->buttonList->remove('save');

        $this->buttonList->update('save', 'onclick', 'order.submit()');
        $this->buttonList->update('save', 'class', 'primary');
        // Temporary solution, unset button widget. Will have to wait till jQuery migration is complete
        $this->buttonList->update('save', 'data_attribute', []);

        $this->buttonList->update('save', 'id', 'submit_order_top_button');
        if ($customerId === null || !$storeId) {
            $this->buttonList->update('save', 'style', 'display:none');
        }

        $this->buttonList->update('back', 'id', 'back_order_top_button');
        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');

        $this->buttonList->update('reset', 'id', 'reset_order_top_button');

        if ($customerId === null) {
            $this->buttonList->update('reset', 'style', 'display:none');
        } else {
            $this->buttonList->update('back', 'style', 'display:none');
        }

        $confirm = __('Are you sure you want to cancel this order?');
        $this->buttonList->update('reset', 'label', __('Cancel'));
        $this->buttonList->update('reset', 'class', 'cancel');
        $this->buttonList->update(
            'reset',
            'onclick',
            'deleteConfirm(\'' . $confirm . '\', \'' . $this->getCancelUrl() . '\')'
        );
    }
}
