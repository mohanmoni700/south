<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Tab\View;

class DeactiveAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * [render]
     *
     * @param  \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /*$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/DeactiveAction.log');
                                    $logger = new \Zend_Log();
                                    $logger->addWriter($writer);
        */
        if (null !== $row->getPaymentMethodCode() && $row->getPaymentMethodCode() == "spreedly") {
            $action = [
                'url' => $this->getUrl(
                    'customercreditbvi/customer/deactivate',
                    ['id' => $row->getId(), 'payment_method' => $row->getPaymentMethodCode()]
                ),
                'caption' => __('Deactivate'),
            ];
            return $this->_toLinkHtml($action, $row);
        } elseif (null !== $row->getMethod() && $row->getMethod() == "paradoxlabs_firstdata") {
            $action = [
                'url' => $this->getUrl(
                    'customercreditbvi/customer/deactivate',
                    ['id' => $row->getId(), 'payment_method' => $row->getMethod()]
                ),
                'caption' => __('Deactivate'),
            ];
            return $this->_toLinkHtml($action, $row);
        }
    }
}
