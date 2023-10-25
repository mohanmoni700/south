<?php

declare(strict_types=1);

namespace Ooka\Catalog\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ooka\Catalog\Logger\Logger;


/**
 * Thank you class for Trigger third email
 */
class Thankyou extends Action
{
    private const XML_PATH_GIFTCARD_EMAIL_TEMPLATE = "giftcard/thankyou_giftcardaccount_email/thankyou_template";

    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    private Logger $logger;


    /**
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     */
    public function __construct(
        Context                      $context,
        TransportBuilder             $transportBuilder,
        ScopeConfigInterface         $scopeConfig,
        OrderItemRepositoryInterface $orderItemRepository,
        StoreManagerInterface        $storeManager,
        Logger                       $logger

    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->orderItemRepositoryInterface = $orderItemRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;

    }

    /**
     * Function for getting order item id,store id,sender email and recipientemail
     */
    public function execute()
    {
        $itemId = $this->getRequest()->getParam('order_item_id');
        $orderItem = $this->orderItemRepositoryInterface->get($itemId);
        $storeId = $orderItem->getStoreId();
        $storeUrl = $this->storeManager->getStore($storeId)->getBaseUrl();
        $senderName = $orderItem->getProductOptionByCode('giftcard_sender_name');
        $senderEmail = $orderItem->getProductOptionByCode('giftcard_sender_email');
        $recipientEmail = $orderItem->getProductOptionByCode('giftcard_recipient_email');
        $recipientName = $orderItem->getProductOptionByCode('giftcard_recipient_name');

        try {

            $templateId = $this->getGiftcardConfig($storeId);
            $sender = [
                'name' => $senderName,
                'email' => $senderEmail,
            ];
            $receiver = [
                'name' => $recipientName,
                'email' => $recipientEmail,
            ];
            $templateVars = [
                'name' => 'Recipient Name',
            ];
            $this->logger->info("Email Data", [
                'order_item_id' => $orderItem,
                'store_id' => $orderItem->getStoreId(),
                'store_url' => $storeUrl,
                'giftcard_sender_name' => $senderName,
                'giftcard_sender_email' => $senderEmail,
                'giftcard_recipient_name' => $recipientName,
                'giftcard_recipient_email' => $recipientEmail,
                'template_id' => $templateId,
            ]);
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($templateVars)
                ->setFrom($receiver)
                ->addTo($sender['email'], $receiver['name'])
                ->getTransport();

            $transport->sendMessage();
            $this->logger->info("Email sent successfully");

            $this->messageManager->addSuccessMessage('Thank you for your order');
        } catch (MailException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->info("Email not sent successfully",[
                'error' => $e->getMessage()]
            );

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
