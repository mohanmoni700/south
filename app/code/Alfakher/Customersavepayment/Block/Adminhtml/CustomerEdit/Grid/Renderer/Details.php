<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer;

use Corra\Spreedly\Model\Ui\ConfigProvider as CorraConfig;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use ParadoxLabs\FirstData\Model\ConfigProvider as ParadoxsConfig;

class Details extends AbstractRenderer
{
    /**
     * [__construct]
     *
     * @param Context $context
     * @param CreditCardTokenFactory $collectionFactory
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        CreditCardTokenFactory $collectionFactory,
        SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
    }
    /**
     * [render]
     *
     * @param  DataObject $row
     * @return mixed
     */
    public function render(DataObject $row)
    {
        if (null !== $row->getPaymentMethodCode() && $row->getPaymentMethodCode() === CorraConfig::CODE) {
            $details = $row->getDetails();
            $separatedetails = $this->serializer->unserialize($details);
            $response = '<p>Type: ' . $separatedetails['type'] .
                ' | CC: ' . $separatedetails['maskedCC'] . ' | Exp Date: ' .
                $separatedetails['expirationDate'] . '</p>';
            return $response;
        } elseif (null !== $row->getMethod() && $row->getMethod() === ParadoxsConfig::CODE) {
            $details = $row->getAdditional();
            $response = '<p> Type: ' . $details['cc_type'] . ' | CC: ' .
                $details['cc_last4'] . ' | Exp Date: ' .
                $details['cc_exp_month'] . '/' . $details['cc_exp_year'] . '</p>';
            return $response;
        }
    }
}
