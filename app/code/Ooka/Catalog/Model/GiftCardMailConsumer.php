<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ooka\Catalog\Model;

use Magento\Store\Model\App\Emulation;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Psr\Log\LoggerInterface;

/**
 * Class GiftCardMailConsumer used to process getting thank you email
 */
class GiftCardMailConsumer
{
    private const XML_PATH_GIFTCARD_EMAIL_TEMPLATE = "giftcard/thankyou_giftcardaccount_email/thankyou_template";

    /**
     * @var Emulation
     */
    private Emulation $appEmulation;
    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepositoryInterface;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Emulation $appEmulation
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param JsonHelper $jsonHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Emulation                    $appEmulation,
        TransportBuilder             $transportBuilder,
        ScopeConfigInterface         $scopeConfig,
        OrderItemRepositoryInterface $orderItemRepository,
        JsonHelper                   $jsonHelper,
        LoggerInterface              $logger
    ) {
        $this->appEmulation = $appEmulation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->orderItemRepositoryInterface = $orderItemRepository;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
    }

    /**
     * Function for getting thank you email
     *
     * @param array $request
     * @return true|void
     */

    public function process($request)
    {
        try {
            $request = $this->jsonHelper->unserialize($request);
            $itemId = $request['order_item_id'];
            $orderItem = $this->orderItemRepositoryInterface->get($itemId);
            $storeId = $orderItem->getStoreId();
            $senderName = $orderItem->getProductOptionByCode('giftcard_sender_name');
            $senderEmail = $orderItem->getProductOptionByCode('giftcard_sender_email');
            $recipientEmail = $orderItem->getProductOptionByCode('giftcard_recipient_email');
            $recipientName = $orderItem->getProductOptionByCode('giftcard_recipient_name');

            $this->appEmulation->startEnvironmentEmulation($storeId);

            $templateId = $this->getGiftcardConfig($storeId);
            $sender = [
                'name' => $senderName,
                'email' => $senderEmail
            ];
            $receiver = [
                'name' => $recipientName,
                'email' => $recipientEmail
            ];
            $templateVars = [
                'name' => $recipientName
            ];

            $this->appEmulation->stopEnvironmentEmulation();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope($receiver)
                ->addTo($sender['email'], $sender['name'])
                ->getTransport();
            $transport->sendMessage();

            return true;
        } catch (\Exception $exception) {
            $this->logger->error("An exception occurred: " . $exception->getMessage());
        }
    }

    /**
     * Function for getting template id
     *
     * @param int $storeId
     * @return mixed
     */
    private function getGiftcardConfig($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GIFTCARD_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
