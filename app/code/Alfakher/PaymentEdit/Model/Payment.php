<?php
declare(strict_types=1);

namespace Alfakher\PaymentEdit\Model;

use Alfakher\PaymentEdit\Model\Gateway;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use MageWorx\OrderEditor\Api\ChangeLoggerInterface;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Model\Payment as BasePayment;
use ParadoxLabs\FirstData\Model\Method;
use ParadoxLabs\TokenBase\Api\CardRepositoryInterface;
use ParadoxLabs\TokenBase\Helper\Address;
use ParadoxLabs\TokenBase\Model\CardFactory;

class Payment extends BasePayment
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CardRepository
     */
    protected $cardRepository;

    /**
     * @var Method
     */
    protected $method;

    /**
     * @var Geteway
     */
    protected $geteway;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var AddressHelper
     */
    protected $addressHelper;

    /**
     * @var customerRepository
     */
    protected $customerRepository;

    /**
     * @var cardFactory
     */
    protected $cardFactory;

    /**
     * Payment constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param QuoteRepositoryInterface $quoteRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param PaymentHelper $paymentHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Http $request
     * @param CardRepositoryInterface $cardRepository
     * @param Method $method
     * @param Gateway $geteway
     * @param Builder $transportBuilder
     * @param ManagerInterface $messageManager
     * @param Address $addressHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CardFactory $cardFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QuoteRepositoryInterface $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        PaymentHelper $paymentHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        Http $request,
        CardRepositoryInterface $cardRepository,
        Method $method,
        Gateway $geteway,
        Builder $transportBuilder,
        ManagerInterface $messageManager,
        Address $addressHelper,
        CustomerRepositoryInterface $customerRepository,
        CardFactory $cardFactory,
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->request = $request;
        $this->cardRepository = $cardRepository;
        $this->method = $method;
        $this->geteway = $geteway;
        $this->transportBuilder = $transportBuilder;
        $this->messageManager = $messageManager;
        $this->addressHelper = $addressHelper;
        $this->customerRepository = $customerRepository;
        $this->cardFactory = $cardFactory;
        parent::__construct(
            $context,
            $registry,
            $quoteRepository,
            $orderRepository,
            $orderPaymentRepository,
            $paymentHelper,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Update payment method
     *
     * @throws LocalizedException
     */
    public function updatePaymentMethod()
    {
        $this->loadOrder();
        $payment = $this->order->getPayment();
        $lastTransId = $payment->getLastTransId();
        $origPayment = $payment->getMethod();
        $payment->setMethod($this->getPaymentMethod());

        $tokenbaseId = '';
        if ($this->getPaymentMethod() == 'paradoxlabs_firstdata') {
            try {
                $cardId = $this->request->getParam('card_id');
                $this->method->gateway();
                if (!empty($cardId)) {
                    $card = $this->cardRepository->getByHash($cardId);
                } else {
                    $ccNumber = $this->request->getParam('cc_number');
                    $ccExpMonth = $this->request->getParam('cc_exp_month');
                    $ccExpYear = $this->request->getParam('cc_exp_year');
                    $ccCid = $this->request->getParam('cc_cid');
                    $sccType = $this->request->getParam('cc_type');
                    $save = $this->request->getParam('save');

                    $cardTypeMap = [
                        'AE' => 'american express',
                        'DI' => 'discover',
                        'DC' => 'diners club',
                        'JCB' => 'jcb',
                        'MC' => 'mastercard',
                        'VI' => 'visa',
                    ];
                    $ccType = $cardTypeMap[$sccType];

                    $billingAddress = $this->addressHelper->buildAddressFromInput(
                        $this->order->getBillingAddress()->getData(),
                        [],
                        true
                    );

                    $cardHolderName = $this->order->getBillingAddress()->getFirstName() . ' ' .
                    $this->order->getBillingAddress()->getLastName();
                    $this->geteway->setParameterForBackend(
                        $ccNumber,
                        $ccExpMonth,
                        $ccExpYear,
                        $ccCid,
                        $ccType,
                        $cardHolderName
                    );
                    $response = $this->geteway->tokenizeCreditCard();
                    if ($response) {
                        $cardData['cc_type'] = $sccType;
                        $cardData['cc_last4'] = substr($ccNumber, -4);
                        $cardData['cc_exp_year'] = $ccExpYear;
                        $cardData['cc_exp_month'] = $ccExpMonth;

                        $customer = $this->customerRepository->getById($this->order->getCustomerId());
                        $card = $this->cardFactory->create();
                        $card->setMethod($this->getPaymentMethod());
                        if ($save === 0) {
                            $card->setActive(0);
                        } else {
                            $card->setActive(1);
                        }
                        $card->setCustomer($customer);
                        $card->setAddress($billingAddress);
                        $card->setData('additional', json_encode($cardData));
                        $card->setPaymentId($response);
                        $card = $this->cardRepository->save($card);
                    } else {
                        $this->messageManager->addErrorMessage(
                            __('Please check credit card number.Payment method change is failed.')
                        );
                        return;
                    }
                }
                $card = $card->getTypeInstance();
                $grandTotal = $this->order->getGrandTotal();
                $this->geteway->setCard($card);
                try {
                    $response = $this->geteway->authorizeBackend($this->order, $grandTotal);
                    if ($origPayment == 'paradoxlabs_firstdata') {
                        $oldCard = $this->cardRepository->load($payment->getTokenbaseId());
                        $oldCard = $oldCard->getTypeInstance();
                        $orderState = $this->order->getState();
                        $orderStatus = $this->order->getStatus();
                        $this->geteway->setCard($oldCard);
                        if (isset($lastTransId) && $lastTransId != "") {
                            $this->geteway->voidBackend($this->order);
                            $this->order->getPayment()->void(new \Magento\Framework\DataObject());
                        }
                        $this->order->setState($orderState)->setStatus($orderStatus);
                        $this->order->save();
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Some error occured please check order comment'));
                    $this->order->addStatusHistoryComment(__('Payment Declined: <b>' . $e->getMessage() . '</b>'));
                    $payment->setMethod($origPayment);
                    $this->order->save();
                    return;
                }
                $tokenbaseId = $card->getId();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return;
            }
        }

        if ($origPayment != $this->getPaymentMethod()) {
            $oldTitle = $this->paymentHelper->getMethodInstance($origPayment)->getTitle();
            $newTitle = $this->paymentHelper->getMethodInstance($this->getPaymentMethod())->getTitle();
                try
                {
                    if (isset($lastTransId) && $lastTransId != "" && $origPayment = "paradoxlabs_firstdata") {
                        $this->method->gateway();
                        $oldCard = $this->cardRepository->load($payment->getTokenbaseId());
                        $oldCard = $oldCard->getTypeInstance();
                        $this->geteway->setCard($oldCard);
                        $this->geteway->voidBackend($this->order);
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return;
                }
            $this->_eventManager->dispatch(
                'mageworx_log_changes_on_order_edit',
                [
                    ChangeLoggerInterface::SIMPLE_MESSAGE_KEY => __(
                        'Payment method has been changed from <b>%1</b> to <b>%2</b>',
                        $oldTitle,
                        $newTitle
                    ),
                ]
            );
        }

        /* Prepare date for additional information */
        if ($this->getPaymentTitle() !== null) {
            $payment->setAdditionalInformation(
                'method_title',
                $this->getPaymentTitle()
            );
        }
        if ($this->getPaymentMethod() == 'paradoxlabs_firstdata') {
            $this->addTransactionToOrder($this->order, $response->getData());
            $payment->setTransactionAdditionalInfo(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $response->getData()
            );

            if ($response->getData('is_fraud') === true) {
                $payment->setIsTransactionPending(true)
                    ->setIsFraudDetected(true)
                    ->setTransactionAdditionalInfo('is_transaction_fraud', true);
            }
            $ccLast4 = substr($response->getData('payment_id'), -4);

            $cardTypeMap = [
                'american express' => 'AE',
                'discover' => 'DI',
                'diners club' => 'DC',
                'jcb' => 'JCB',
                'mastercard' => 'MC',
                'visa' => 'VI',
            ];

            $ccType = $cardTypeMap[$response->getCardType()];
            $payment->setTransactionId($response->getData('transaction_id'))
                ->setCcType($ccType)
                ->setCcLast4($ccLast4)
                ->setTokenbaseId($tokenbaseId)
                ->setAdditionalInformation(
                    array_replace_recursive($payment->getAdditionalInformation(), $response->getData())
                )
                ->setIsTransactionClosed(0);
        }

        $payment = $this->order->setPayment($payment);
        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($this->order);

        /* change data in quote */
        $quote = $this->getQuote();
        $payment = $quote->getPayment();
        $payment->setMethod($this->getPaymentMethod());
        if ($this->getPaymentTitle() !== null) {
            $payment->setAdditionalInformation(
                'method_title',
                $this->getPaymentTitle()
            );
        }
        if ($this->getPaymentMethod() == 'paradoxlabs_firstdata') {
            $payment->setTransactionId($response->getData('transaction_id'))
                ->setCcType($ccType)
                ->setCcLast4($ccLast4)
                ->setTokenbaseId($tokenbaseId)
                ->setAdditionalInformation(
                    array_replace_recursive($payment->getAdditionalInformation(), $response->getData())
                )
                ->setIsTransactionClosed(0);
        }
        $payment->save();
        $this->messageManager->addSuccessMessage("Payment method is changed successfully.");

        $this->_eventManager->dispatch(
            'mageworx_save_logged_changes_for_order',
            [
                'order_id' => $this->order->getId(),
                'notify_customer' => false,
            ]
        );
    }

    /**
     * Add Transaction To Order
     *
     * @param object $order
     * @param array $paymentData
     */
    public function addTransactionToOrder($order, $paymentData = [])
    {
        try {
            // Prepare payment object
            $payment = $order->getPayment();
            $payment->setMethod('paradoxlabs_firstdata');
            $payment->setLastTransId($paymentData['transaction_id']);
            $payment->setTransactionId($paymentData['transaction_id']);
            $payment->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $paymentData]);
            $payment->setIsTransactionClosed(0);

            $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

            $transaction = $this->transportBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['transaction_id'])
                ->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $paymentData])
                ->setFailSafe(true)
                ->build(Transaction::TYPE_AUTH);

            // Add transaction to payment
            $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1.', $formatedPrice));
            $payment->setParentTransactionId(null);

            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();
            return $transaction->getTransactionId();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}

