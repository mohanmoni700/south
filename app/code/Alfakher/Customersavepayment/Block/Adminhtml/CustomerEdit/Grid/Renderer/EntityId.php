<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class EntityId extends AbstractRenderer
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
            $entityId = $row->getEntityId();
            return $entityId;
        } elseif (null !== $row->getMethod() && $row->getMethod() == "paradoxlabs_firstdata") {
            $entityId = $row->getId();
            return $entityId;
        }
    }
}
