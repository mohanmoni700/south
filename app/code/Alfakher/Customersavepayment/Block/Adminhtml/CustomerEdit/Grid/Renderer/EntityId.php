<?php

namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Model\CreditCardTokenFactory;

class EntityId extends AbstractRenderer
{
    public const SPEEDLY_PAYMENT_CODE = "spreedly";
    public const PARADOXLABS_PAYMENT_CODE = "paradoxlabs_firstdata";

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
        if (null !== $row->getPaymentMethodCode() && $row->getPaymentMethodCode() === self::SPEEDLY_PAYMENT_CODE) {
            $entityId = $row->getEntityId();
            return $entityId;
        } elseif (null !== $row->getMethod() && $row->getMethod() === self::PARADOXLABS_PAYMENT_CODE) {
            $entityId = $row->getId();
            return $entityId;
        }
    }
}
