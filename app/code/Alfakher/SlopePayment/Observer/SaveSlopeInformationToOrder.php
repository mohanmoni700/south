<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Observer;

use Alfakher\SlopePayment\Model\Payment\SlopePayment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Alfakher\SlopePayment\Helper\Config as SlopeConfigHelper;
use Alfakher\SlopePayment\Model\Gateway\Request as GatewayRequest;
use Magento\Framework\Serialize\Serializer\Json;

class SaveSlopeInformationToOrder implements ObserverInterface
{
    public const UPDATE_ORDER = '/orders/id/';

    /**
     * Quotes repository
     *
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var SlopeConfigHelper
     */
    protected $slopeConfig;

    /**
     * @var GatewayRequest
     */
    protected $gatewayRequest;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Class constructor
     *
     * @param QuoteRepository $quoteRepository
     * @param SlopeConfigHelper $slopeConfig
     * @param GatewayRequest $gatewayRequest
     * @param Json $json
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        SlopeConfigHelper $slopeConfig,
        GatewayRequest $gatewayRequest,
        Json $json
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->config = $slopeConfig;
        $this->gatewayRequest = $gatewayRequest;
        $this->json = $json;
    }

    /**
     * Save slope information to order
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethodCode = $order->getPayment()->getMethod();
        if ($paymentMethodCode == SlopePayment::PAYMENT_METHOD_SLOPEPAYMENT_CODE) {
            $oldExternalId = $order->getQuoteId();
            $newExternalId = $order->getIncrementId();
            $response = $this->updateSlopeOrderExternalId($oldExternalId, $newExternalId);
            $order->setSlopeInformation(serialize($response));// phpcs:disable
            $order->save();
        }
    }

    /**
     * Update slope order externalId
     *
     * @param int $oldExternalId
     * @param int $newExternalId
     * @return array
     */
    public function updateSlopeOrderExternalId($oldExternalId, $newExternalId)
    {
        $data = array("externalId" => $newExternalId);
        $data = json_encode($data);
        $apiEndpointUrl = $this->config->getEndpointUrl();
        $url = $apiEndpointUrl . self::UPDATE_ORDER;
        $url = str_replace("id", $oldExternalId, $url);
        $response = $this->gatewayRequest->post($url, $data);
        $response = $this->json->unserialize($response);
        return $response;
    }
}
