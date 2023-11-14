<?php

declare(strict_types=1);

namespace Ooka\Catalog\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ooka\Catalog\Logger\Logger;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Thank you class for Trigger thank you email
 */
class Thankyou extends Action
{
    private const XML_PATH_GIFTCARD_EMAIL_TEMPLATE = "giftcard/thankyou_giftcardaccount_email/thankyou_template";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepo;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Logger
     */
    private Logger $logger;
    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderItemRepositoryInterface $orderItemRepo
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param PublisherInterface $publisher
     */
    public function __construct(
        Context                      $context,
        ScopeConfigInterface         $scopeConfig,
        OrderItemRepositoryInterface $orderItemRepo,
        StoreManagerInterface        $storeManager,
        Logger                       $logger,
        PublisherInterface           $publisher
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->orderItemRepo = $orderItemRepo;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    /**
     * Function for getting order item id,store id,sender email and recipientemail
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('order_item_id');
        $orderItem = $this->orderItemRepo->get($itemId);
        $storeId = $orderItem->getStoreId();
        $storeUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        $senderName = $orderItem->getProductOptionByCode('giftcard_sender_name');
        $senderEmail = $orderItem->getProductOptionByCode('giftcard_sender_email');
        $recipientEmail = $orderItem->getProductOptionByCode('giftcard_recipient_email');
        $recipientName = $orderItem->getProductOptionByCode('giftcard_recipient_name');

        try {

            $templateId = $this->getGiftcardConfig($storeId);

            $details = ['order_item_id' => $itemId,
                'store_id' => $orderItem->getStoreId(),
                'store_url' => $storeUrl,
                'giftcard_sender_name' => $senderName,
                'giftcard_sender_email' => $senderEmail,
                'giftcard_recipient_name' => $recipientName,
                'giftcard_recipient_email' => $recipientEmail,
                'template_id' => $templateId,
            ];

            $this->publisher->publish(
                'notifycustomer.thankyoumail',
                json_encode($details)
            );

            $this->logger->info("Email Data", $details);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->info($e->getMessage());

            $this->messageManager->addErrorMessage('Email Not Sent');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($storeUrl);

        return $resultRedirect;
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
