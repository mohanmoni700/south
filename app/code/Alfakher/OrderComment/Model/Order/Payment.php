<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Alfakher\OrderComment\Model\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Payment\Model\SaleOperationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Info;
use Magento\Sales\Model\Order\Payment\Operations\SaleOperation;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;
use Magento\Sales\Api\CreditmemoManagementInterface as CreditmemoManager;
use Magento\Sales\Model\Order\OrderStateResolverInterface;

/**
 * Order payment information
 *
 * @api
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Payment extends \Magento\Sales\Model\Order\Payment
{
    /**
     * Actions for payment when it triggered review state
     *
     * @var string
     */
    const REVIEW_ACTION_ACCEPT = 'accept';

    const REVIEW_ACTION_DENY = 'deny';

    const REVIEW_ACTION_UPDATE = 'update';

    const PARENT_TXN_ID = 'parent_transaction_id';

    /**
     * Order model object
     *
     * @var Order
     */
    protected $_order;

    /**
     * Whether can void
     * @var string
     */
    protected $_canVoidLookup = null;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_payment';

    /**
     * @var string
     */
    protected $_eventObject = 'payment';

    /**
     * Transaction additional information container
     *
     * @var array
     */
    protected $transactionAdditionalInfo = [];

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface
     */
    protected $transactionManager;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var Payment\Processor
     */
    protected $orderPaymentProcessor;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderStateResolverInterface
     */
    private $orderStateResolver;

    /**
     * @var CreditmemoManager
     */
    private $creditmemoManager = null;

    /**
     * @var SaleOperation
     */
    private $saleOperation;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface $transactionManager
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param Payment\Processor $paymentProcessor
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param CreditmemoManager $creditmemoManager
     * @param SaleOperation $saleOperation
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        ManagerInterface $transactionManager,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Sales\Model\Order\Payment\Processor $paymentProcessor,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        CreditmemoManager $creditmemoManager = null,
        SaleOperation $saleOperation = null
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->transactionRepository = $transactionRepository;
        $this->transactionManager = $transactionManager;
        $this->transactionBuilder = $transactionBuilder;
        $this->orderPaymentProcessor = $paymentProcessor;
        $this->orderRepository = $orderRepository;
        $this->creditmemoManager = $creditmemoManager ?: ObjectManager::getInstance()->get(CreditmemoManager::class);
        $this->saleOperation = $saleOperation ?: ObjectManager::getInstance()->get(SaleOperation::class);

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $encryptor,
            $creditmemoFactory,
            $priceCurrency,
            $transactionRepository,
            $transactionManager,
            $transactionBuilder,
            $paymentProcessor,
            $orderRepository,
            $resource,
            $resourceCollection,
            $data,
            $creditmemoManager,
            $saleOperation
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Payment::class);
    }

    /**
     * Refund payment online or offline, depending on whether there is invoice set in the creditmemo instance
     * Updates transactions hierarchy, if required
     * Updates payment totals, updates order status and adds proper comments
     *
     * @param Creditmemo $creditmemo
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function refund($creditmemo)
    {
        $baseAmountToRefund = $this->formatAmount($creditmemo->getBaseGrandTotal());
        $this->setTransactionId(
            $this->transactionManager->generateTransactionId($this, Transaction::TYPE_REFUND)
        );

        $isOnline = false;
        $gateway = $this->getMethodInstance();
        $invoice = null;
        if ($gateway->canRefund()) {
            $this->setCreditmemo($creditmemo);
            if ($creditmemo->getDoTransaction()) {
                $invoice = $creditmemo->getInvoice();
                if ($invoice) {
                    $isOnline = true;
                    $captureTxn = $this->transactionRepository->getByTransactionId(
                        $invoice->getTransactionId(),
                        $this->getId(),
                        $this->getOrder()->getId()
                    );
                    if ($captureTxn) {
                        $this->setTransactionIdsForRefund($captureTxn);
                    }
                    $this->setShouldCloseParentTransaction(true);
                    // TODO: implement multiple refunds per capture
                    try {
                        $gateway->setStore(
                            $this->getOrder()->getStoreId()
                        );
                        $this->setRefundTransactionId($invoice->getTransactionId());
                        $gateway->refund($this, $baseAmountToRefund);

                        $creditmemo->setTransactionId($this->getLastTransId());
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        if (!$captureTxn) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('If the invoice was created offline, try creating an offline credit memo.'),
                                $e
                            );
                        }
                        throw $e;
                    }
                }
            } elseif ($gateway->isOffline()) {
                $gateway->setStore(
                    $this->getOrder()->getStoreId()
                );
                $gateway->refund($this, $baseAmountToRefund);
            }
        }

        // update self totals from creditmemo
        $this->_updateTotals(
            [
                'amount_refunded' => $creditmemo->getGrandTotal(),
                'base_amount_refunded' => $baseAmountToRefund,
                'base_amount_refunded_online' => $isOnline ? $baseAmountToRefund : null,
                'shipping_refunded' => $creditmemo->getShippingAmount(),
                'base_shipping_refunded' => $creditmemo->getBaseShippingAmount(),
            ]
        );

        // update transactions and order state
        $transaction = $this->addTransaction(
            Transaction::TYPE_REFUND,
            $creditmemo,
            $isOnline
        );

        $message = '';
        if ($invoice) {
            $message = __('We refunded %1 online.', $this->formatPrice($baseAmountToRefund));
        } else {

            if(isset($_POST['creditmemo']) && empty($_POST['creditmemo']['refund_customerbalance_return_enable'])) {
                $message = $this->hasMessage() ? $this->getMessage() : __(
                    'We refunded %1 offline.',
                    $this->formatPrice($baseAmountToRefund)
                );
            }
        }
        $message = $this->prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $orderState = $this->getOrderStateResolver()->getStateForOrder($this->getOrder());
        $statuses = $this->getOrder()->getConfig()->getStateStatuses($orderState, false);
        $status = in_array($this->getOrder()->getStatus(), $statuses, true)
            ? $this->getOrder()->getStatus()
            : $this->getOrder()->getConfig()->getStateDefaultStatus($orderState);
        $this->getOrder()
            ->addStatusHistoryComment(
                $message,
                $status
            )->setIsCustomerNotified($creditmemo->getOrder()->getCustomerNoteNotify());
        $this->_eventManager->dispatch(
            'sales_order_payment_refund',
            ['payment' => $this, 'creditmemo' => $creditmemo]
        );

        return $this;
    }

    /**
     * Totals updater utility method
     *
     * Updates self totals by keys in data array('key' => $delta)
     *
     * @param array $data
     * @return void
     */
    protected function _updateTotals($data)
    {
        foreach ($data as $key => $amount) {
            if (null !== $amount) {
                $was = $this->getDataUsingMethod($key);
                $this->setDataUsingMethod($key, $was + $amount);
            }
        }
    }

    /**
     * Append transaction ID (if any) message to the specified message
     *
     * @param Transaction|null $transaction
     * @param string $message
     * @return string
     */
    protected function _appendTransactionToMessage($transaction, $message)
    {
        if ($transaction) {
            $txnId = is_object($transaction) ? $transaction->getHtmlTxnId() : $transaction;
            $message .= ' ' . __('Transaction ID: "%1"', $txnId);
        }

        return $message;
    }

    /**
     * Prepare credit memo
     *
     * @param float $amount
     * @param float $baseGrandTotal
     * @param false|Invoice $invoice
     * @return mixed
     */
    protected function prepareCreditMemo($amount, $baseGrandTotal, $invoice)
    {
        $entity = $invoice ?: $this->getOrder();
        if ($entity->getBaseTotalRefunded() > 0) {
            $adjustment = ['adjustment_positive' => $amount];
        } else {
            $adjustment = ['adjustment_negative' => $baseGrandTotal - $amount];
        }
        if ($invoice) {
            $creditMemo = $this->creditmemoFactory->createByInvoice($invoice, $adjustment);
        } else {
            $creditMemo = $this->creditmemoFactory->createByOrder($this->getOrder(), $adjustment);
        }
        if ($creditMemo) {
            $totalRefunded = $entity->getBaseTotalRefunded() + $creditMemo->getBaseGrandTotal();
            $this->setShouldCloseParentTransaction($entity->getBaseGrandTotal() <= $totalRefunded);
        }

        return $creditMemo;
    }

    /**
     * Checks if transaction exists
     *
     * @return bool
     */
    protected function checkIfTransactionExists()
    {
        return $this->transactionManager->isTransactionExists(
            $this->getTransactionId(),
            $this->getId(),
            $this->getOrder()->getId()
        );
    }

    /**
     * Get order state resolver instance.
     *
     * @deprecated 101.0.0
     * @return OrderStateResolverInterface
     */
    private function getOrderStateResolver()
    {
        if ($this->orderStateResolver === null) {
            $this->orderStateResolver = ObjectManager::getInstance()->get(OrderStateResolverInterface::class);
        }

        return $this->orderStateResolver;
    }
    private function setTransactionIdsForRefund(Transaction $transaction)
    {
        if (!$this->getTransactionId()) {
            $this->setTransactionId(
                $this->transactionManager->generateTransactionId(
                    $this,
                    Transaction::TYPE_REFUND,
                    $transaction
                )
            );
        }
        $this->setParentTransactionId($transaction->getTxnId());
    }
}
