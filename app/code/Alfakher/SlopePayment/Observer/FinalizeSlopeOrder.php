<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Observer;

use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Alfakher\SlopePayment\Model\Gateway\Request as GatewayRequest;
use Alfakher\SlopePayment\Model\Payment\SlopePayment;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\Serialize\SerializerInterface;

class FinalizeSlopeOrder implements ObserverInterface
{
    public const FINALIZE_ORDER = '/orders/id/finalize';

    /**
     * SlopeConfigHelper
     *
     * @var $SlopeConfigHelper
     */
    protected $slopeConfig;

    /**
     * @var GatewayRequest
     */
    protected $gatewayRequest;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Class constructor
     *
     * @param SlopeConfigHelper $slopeConfig
     * @param GatewayRequest $gatewayRequest
     * @param ManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param Transaction $transaction
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SlopeConfigHelper $slopeConfig,
        GatewayRequest $gatewayRequest,
        ManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction,
        SerializerInterface $serializer
    ) {
        $this->config = $slopeConfig;
        $this->gatewayRequest = $gatewayRequest;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->serializer = $serializer;
    }

    /**
     * After shippment finalize slope order
     *
     * @param Observer $observer
     * @return void
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $paymentMethodCode = $order->getPayment()->getMethod();
        if ($paymentMethodCode == SlopePayment::PAYMENT_METHOD_SLOPEPAYMENT_CODE) {
            try {
                $externalId = $order->getIncrementId();
                $resp = $this->finalizeSlopeOrder($externalId);
                if (isset($resp)) {
                    if (isset($resp['statusCode'])) {
                        $order->addCommentToStatusHistory(
                            __('Slope Payment Error (%1) : %2', $resp['code'], implode(',', $resp['messages'])),
                            true,
                            false
                        );
                        $this->messageManager->addErrorMessage(
                            __('Slope Payment Error (%1) : %2', $resp['code'], implode(',', $resp['messages']))
                        );
                    } else {
                        $order->setSlopeInformation($this->serializer->serialize($resp));
                        $order->addCommentToStatusHistory(
                            __('Slope order %1 finalized successfully.', $resp['id']),
                            true,
                            false
                        );
                        $this->messageManager->addSuccessMessage(
                            __('Slope order %1 finalized successfully.', $resp['id'])
                        );
                        $this->generateSlopeOrderInvoice($order->getId());
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage('Slope Payment Error : ' . $e->getMessage());
            }
        }
    }

    /**
     * Finalize slope order by external id
     *
     * @param int $slopeOrderExternalId
     * @return Json
     */
    public function finalizeSlopeOrder($slopeOrderExternalId)
    {
        $apiEndpointUrl = $this->config->getEndpointUrl();
        $url = $apiEndpointUrl . self::FINALIZE_ORDER;
        $url = str_replace("id", $slopeOrderExternalId, $url);
        $response = $this->gatewayRequest->post($url);
        $response = $this->serializer->unserialize($response);
        return $response;
    }

    /**
     * Generate slope order invoice once order is finalized
     *
     * @param  int $orderId
     * @return void
     */
    public function generateSlopeOrderInvoice($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();

            $transactionSave =
            $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);

            $order->addCommentToStatusHistory(
                __('Notified customer about invoice creation #%1.', $invoice->getId())
            )->setIsCustomerNotified(true)->save();
        }
    }
}
