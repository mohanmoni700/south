<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Details extends AbstractRenderer
{
    /**
     * [__construct]
     *
     * @param Context $context
     * @param \Magento\Vault\Model\CreditCardTokenFactory $collectionFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Vault\Model\CreditCardTokenFactory $collectionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
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
        if (null !== $row->getPaymentMethodCode() && $row->getPaymentMethodCode() == "spreedly") {
            $details = $row->getDetails();
            $separatedetails = $this->serializer->unserialize($details);
            $response = '<p>Type: ' . $separatedetails['type'] .
                ' | CC: ' . $separatedetails['maskedCC'] . ' | Exp Date: ' .
                $separatedetails['expirationDate'] . '</p>';
            return $response;
        } elseif (null !== $row->getMethod() && $row->getMethod() == "paradoxlabs_firstdata") {
            $details = $row->getAdditional();
            $response = '<p> Type: ' . $details['cc_type'] . ' | CC: ' .
                $details['cc_last4'] . ' | Exp Date: ' .
                $details['cc_exp_month'] . '/' . $details['cc_exp_year'] . '</p>';
            return $response;
        }
    }
}
