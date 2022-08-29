<?php
namespace Alfakher\PaymentMethod\Block\Form;

class OfflinePaypal extends \Magento\Payment\Block\Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'Alfakher_PaymentMethod::form/offlinepaypal.phtml';
}
