<?php

declare(strict_types=1);

namespace Alfakher\PaymentEdit\Block\Adminhtml;

use MageWorx\OrderEditor\Model\Order;
use MageWorx\OrderEditor\Model\Quote;
use Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method\Form as PaymentMethodForm;
use MageWorx\OrderEditor\Model\Ui\ConfigProvider;
use MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment\Method as BaseMethod;
use MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment;
use Magento\Payment\Helper\Data;
use MageWorx\OrderEditor\Helper\Data as HelperData;
use Magento\Payment\Model\MethodList;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Backend\Model\Session\Quote as SessionQuote;

class Method extends BaseMethod
{
    /**
     * @var PaymentMethodListInterface
     */
    public $paymentMethodList;

    /**
     * @var InstanceFactory
     */
    public $paymentMethodInstanceFactory;

    /**
     * Method constructor.
     *
     * @param Context $context
     * @param Data $paymentHelper
     * @param SpecificationFactory $methodSpecificationFactory
     * @param SessionQuote $sessionQuote
     * @param Payment $payment
     * @param HelperData $helperData
     * @param MethodList $methodList
     * @param ModuleListInterface $moduleList
     * @param PaymentMethodListInterface $paymentMethodList
     * @param InstanceFactory $paymentMethodInstanceFactory
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        Context $context,
        Data $paymentHelper,
        SpecificationFactory $methodSpecificationFactory,
        SessionQuote $sessionQuote,
        Payment $payment,
        HelperData $helperData,
        MethodList $methodList,
        ModuleListInterface $moduleList,
        PaymentMethodListInterface $paymentMethodList,
        InstanceFactory $paymentMethodInstanceFactory,
        array $data = [],
        array $additionalChecks = []
    ) {
        $this->paymentMethodList            = $paymentMethodList;
        $this->paymentMethodInstanceFactory = $paymentMethodInstanceFactory;
        parent::__construct(
            $context,
            $paymentHelper,
            $methodSpecificationFactory,
            $sessionQuote,
            $payment,
            $helperData,
            $methodList,
            $moduleList,
            $paymentMethodList,
            $paymentMethodInstanceFactory,
            $data
        );
    }

    /**
     * Get offline payment method
     *
     * @return array|mixed|null
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $quote   = $this->getQuote();
            $store   = $quote ? $quote->getStoreId() : null;
            $methods = [];
            foreach ($this->paymentMethodList->getActiveList($store) as $method) {
                $methodInstance = $this->paymentMethodInstanceFactory->create($method);
                if ($methodInstance->isAvailable($quote)
                    && $this->_canUseMethod($methodInstance)
                    && ($methodInstance->isOffline() || $method->getCode() == 'paradoxlabs_firstdata')
                ) {
                    $this->_assignMethod($methodInstance);
                    $methods[] = $methodInstance;
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }
}
