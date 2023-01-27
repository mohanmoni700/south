<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Observer;

use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Alfakher\SlopePayment\Model\Gateway\Request as GatewayRequest;
use Alfakher\SlopePayment\Model\Payment\SlopePayment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

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
     * @var Json
     */
    protected $json;

    /**
     * @var GatewayRequest
     */
    protected $gatewayRequest;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Class constructor
     *
     * @param SlopeConfigHelper $slopeConfig
     * @param Json $json
     * @param GatewayRequest $gatewayRequest
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SlopeConfigHelper $slopeConfig,
        Json $json,
        GatewayRequest $gatewayRequest,
        ManagerInterface $messageManager
    ) {
        $this->config = $slopeConfig;
        $this->json = $json;
        $this->gatewayRequest = $gatewayRequest;
        $this->messageManager = $messageManager;
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
                $quoteId = $order->getQuoteId();
                $resp = $this->finalizeSlopeOrder($quoteId);
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
                        $order->addCommentToStatusHistory(
                            __('Slope order %1 finalized successfully.', $resp['id']),
                            true,
                            false
                        );
                        $this->messageManager->addSuccessMessage(
                            __('Slope order %1 finalized successfully.', $resp['id'])
                        );
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
        $response = $this->json->unserialize($response);
        return $response;
    }
}
