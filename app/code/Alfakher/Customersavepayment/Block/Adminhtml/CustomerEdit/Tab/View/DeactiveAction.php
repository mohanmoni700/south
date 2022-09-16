<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Tab\View;

class DeactiveAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    public const SPEEDLY_PAYMENT_CODE = "spreedly";
    public const PARADOXLABS_PAYMENT_CODE = "paradoxlabs_firstdata";
    /**
     * [render]
     *
     * @param  \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (null !== $row->getPaymentMethodCode() && $row->getPaymentMethodCode() === self::SPEEDLY_PAYMENT_CODE) {
            $action = [
                'url' => $this->getUrl(
                    'customercredithistory/customer/deactivate',
                    ['id' => $row->getId(), 'payment_method' => $row->getPaymentMethodCode()]
                ),
                'caption' => __('Deactivate'),
            ];
            return $this->_toLinkHtml($action, $row);
        } elseif (null !== $row->getMethod() && $row->getMethod() === self::PARADOXLABS_PAYMENT_CODE) {
            $action = [
                'url' => $this->getUrl(
                    'customercredithistory/customer/deactivate',
                    ['id' => $row->getId(), 'payment_method' => $row->getMethod()]
                ),
                'caption' => __('Deactivate'),
            ];
            return $this->_toLinkHtml($action, $row);
        }
    }
}
