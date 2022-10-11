<?php
namespace Alfakher\PaymentEdit\Block\Adminhtml;

use MageWorx\OrderEditor\Model\Order;
use MageWorx\OrderEditor\Model\Quote;
use Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method\Form as PaymentMethodForm;
use MageWorx\OrderEditor\Model\Ui\ConfigProvider;

class Method extends \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment\Method
{

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment
     */
    protected $payment;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \MageWorx\OrderEditor\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Payment\Model\MethodList
     */
    private $methodList;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    public $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory
     */
    public $paymentMethodInstanceFactory;

    /**
     * Method constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment $payment
     * @param \MageWorx\OrderEditor\Helper\Data $helperData
     * @param \Magento\Payment\Model\MethodList $methodList
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList
     * @param \Magento\Payment\Model\Method\InstanceFactory $paymentMethodInstanceFactory
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Payment $payment,
        \MageWorx\OrderEditor\Helper\Data $helperData,
        \Magento\Payment\Model\MethodList $methodList,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Payment\Api\PaymentMethodListInterface $paymentMethodList,
        \Magento\Payment\Model\Method\InstanceFactory $paymentMethodInstanceFactory,
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
