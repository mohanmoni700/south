<?php
declare(strict_types=1);

namespace Alfakher\PaymentEdit\Block\Adminhtml;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Form as SaleForm;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\OrderFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Framework\Json\EncoderInterface;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Customer\Model\Address\Mapper;

class Form extends SaleForm
{

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param EncoderInterface $jsonEncoder
     * @param FormFactory $customerFormFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CurrencyInterface $localeCurrency
     * @param Address\Mapper $addressMapper
     * @param Http $request
     * @param OrderFactory $orderFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        EncoderInterface $jsonEncoder,
        FormFactory $customerFormFactory,
        CustomerRepositoryInterface $customerRepository,
        CurrencyInterface $localeCurrency,
        Mapper $addressMapper,
        Http $request,
        OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->request = $request;
        $this->orderFactory = $orderFactory;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $jsonEncoder,
            $customerFormFactory,
            $customerRepository,
            $localeCurrency,
            $addressMapper,
            $data
        );
    }

    /**
     * Get order data jason
     *
     * @return string
     */
    public function getOrderViewDataJson()
    {
        $orderId = $this->request->getParam('order_id');
        $data = [];
        if ($orderId) {
            $order = $this->orderFactory->create()->load($orderId);
            $data['customer_id'] = $order->getCustomerId();
            $data['addresses'] = [];

            $addresses = $this->customerRepository->getById($order->getCustomerId())->getAddresses();

            foreach ($addresses as $address) {
                $addressForm = $this->_customerFormFactory->create(
                    'customer_address',
                    'adminhtml_customer_address',
                    $this->addressMapper->toFlatArray($address)
                );
                $data['addresses'][$address->getId()] = $addressForm->outputData(
                    \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
                );
            }
            if ($order->getStoreId() !== null) {
                $data['store_id'] = $order->getStoreId();
                $currency = $this->_localeCurrency->getCurrency($order->getOrderCurrencyCode());
                $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
                $data['currency_symbol'] = $symbol;
                $data['shipping_method_reseted'] = !(bool)$order->getShippingMethod();
                $data['payment_method'] = $order->getPayment()->getMethod();
            }
        }
        $data['quote_id'] = $order->getQuoteId();

        return $this->_jsonEncoder->encode($data);
    }
}
