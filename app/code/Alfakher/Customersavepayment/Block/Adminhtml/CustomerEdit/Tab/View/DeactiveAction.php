<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Tab\View;

use Corra\Spreedly\Model\Ui\ConfigProvider as CorraConfig;
use ParadoxLabs\FirstData\Model\ConfigProvider as ParadoxsConfig;

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
        if (null !== $row->getPaymentMethodCode() && $row->getPaymentMethodCode() === CorraConfig::CODE) {
            $action = [
                'url' => $this->getUrl(
                    'customercredithistory/customer/deactivate',
                    ['id' => $row->getId(), 'payment_method' => $row->getPaymentMethodCode()]
                ),
                'caption' => __('Deactivate'),
            ];
            return $this->_toLinkHtml($action, $row);
        } elseif (null !== $row->getMethod() && $row->getMethod() === ParadoxsConfig::CODE) {
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
