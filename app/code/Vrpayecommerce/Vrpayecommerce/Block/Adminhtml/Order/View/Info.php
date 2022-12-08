<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Block\Adminhtml\Order\View;

class Info extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * get additional information
     * @return array
     */
    public function getArrayAdditionalInformation()
    {
        $additionalInformation = $this->getOrder()->getPayment()->getAdditionalInformation();
        foreach ($additionalInformation as $key => $value) {
            $informationItem[$key] = $value;
        }
        if (isset($informationItem['RISK_SCORE'])) {
            $informationItem['RISK_SCORE'] = $this->getRiskScorePayment($informationItem['RISK_SCORE']);
        }
        return $informationItem;
    }

    /**
     * get a risk score payment
     * @param  int $riskScore
     * @return string
     */
    public function getRiskScorePayment($riskScore)
    {
        if ($riskScore >= 0) {
            return "<span class='success'>Success</span>";
        }

        return "<span class='failed'>Failed</span>";
    }

    /**
     *  get an update order URL
     * @return string
     */
    public function getUpdateOrderUrl()
    {
        $orderId = $this->_request->getParam('order_id');

        return $this->getUrl('vrpayecommerce/order/update', ['order_id' => $orderId, '_secure' => true]);
    }
}
