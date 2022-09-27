<?php

namespace Alfakher\Seamlesschex\Block\Info;

class Instructions extends \Magento\Payment\Block\Info
{
    /**
     * Prepare information
     *
     * @param null|\Magento\Framework\DataObject|array $transport
     */
    protected function _prepareSpecificInformation(
        $transport = null
    ) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        $additionalData = $this->getInfo()->getAdditionalInformation();
        $data[(string)__('Account Number')]   = $additionalData['check']['bank_account'];
        $data[(string)__('Routing Number')]   = $additionalData['check']['bank_routing'];
        $data[(string)__('Check Number')]   = $additionalData['check']['number'];

        $transport->setData(array_merge($data, $transport->getData()));
        return $transport;
    }
}
