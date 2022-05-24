<?php

namespace Alfakher\Webhook\Helper;

use Magento\Framework\DataObject;
use Exception;
use Liquid\Template;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Mageplaza\Webhook\Model\Config\Source\Authentication;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Config\Source\Schedule;
use Mageplaza\Webhook\Model\Config\Source\Status;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\ResourceModel\Hook\Collection;
use Zend_Http_Response;
use Magento\Framework\App\State;

class Data extends \Mageplaza\Webhook\Helper\Data
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * Data constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Mageplaza\Webhook\Block\Adminhtml\LiquidFilters $liquidFilters
     * @param \Mageplaza\Webhook\Model\HookFactory $hookFactory
     * @param \Mageplaza\Webhook\Model\HistoryFactory $historyFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customer
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        UrlInterface $backendUrl,
        TransportBuilder $transportBuilder,
        CurlFactory $curlFactory,
        LiquidFilters $liquidFilters,
        HookFactory $hookFactory,
        HistoryFactory $historyFactory,
        CustomerRepositoryInterface $customer,
        State $state
    ) {
        $this->state = $state;
        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $backendUrl,
            $transportBuilder,
            $curlFactory,
            $liquidFilters,
            $hookFactory,
            $historyFactory,
            $customer
        );
    }

    /**
     * @inheritDoc
     */
    public function sendDocument($item, $hookType)
    {
        if (!$this->isEnabled()) {
            return;
        }
        $hookCollection = $this->hookFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', $hookType)
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('store_ids', [
                ['finset' => Store::DEFAULT_STORE_ID],
                ['finset' => $this->getItemStore($item)]
            ])
            ->setOrder('priority', 'ASC');
        $isSendMail     = $this->getConfigGeneral('alert_enabled');
        $sendTo         = explode(',', $this->getConfigGeneral('send_to'));
        foreach ($hookCollection as $hook) {
            if ($hook->getHookType() === HookType::ORDER) {
                $statusItem  = $item->getStatus();
                $orderStatus = explode(',', $hook->getOrderStatus());
                if (!in_array($statusItem, $orderStatus, true)) {
                    continue;
                }
            }
            $history = $this->historyFactory->create();

            if ($hookType == "update_document" || $hookType == "new_document") {
                $documentData = $this->AddFilePath($item, $hookType);
                $documenItem = new DataObject($documentData);
            } elseif ($hookType == "delete_document") {
                $documentData = $this->AddFilePath($item, $hookType);
                $documenItem = new DataObject($documentData['items']);
            }
            
            $body = $this->generateLiquidTemplate($documenItem, $hook->getBody());
            $data    = [
                'hook_id'     => $hook->getId(),
                'hook_name'   => $hook->getName(),
                'store_ids'   => $hook->getStoreIds(),
                'hook_type'   => $hook->getHookType(),
                'priority'    => $hook->getPriority(),
                'payload_url' => $this->generateLiquidTemplate($documenItem, $hook->getPayloadUrl()),
                'body'        => $this->generateLiquidTemplate($documenItem, $hook->getBody())
            ];
            $history->addData($data);
            try {
                $result = $this->sendHttpRequestFromHook($hook, $documenItem);
                $history->setResponse(isset($result['response']) ? $result['response'] : '');
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
            if ($result['success'] === true) {
                $history->setStatus(Status::SUCCESS);
            } else {
                $history->setStatus(Status::ERROR)
                    ->setMessage($result['message']);
                if ($isSendMail) {
                    $this->sendMail(
                        $sendTo,
                        __('Something went wrong while sending %1 hook', $hook->getName()),
                        $this->getConfigGeneral('email_template'),
                        $this->getStoreId()
                    );
                }
            }

            $history->save();
        }
    }

    /**
     * Add full image or pdf path instead of name
     *
     * @param array $items
     * @param string $hookType
     * @return array
     */
    public function addFilePath($items, $hookType)
    {
        if ($hookType == "delete_document") {
            $items['items']['filename'] =
                $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                )."myDocument/".$items['items']['filename'];
        } else {
            foreach ($items['items'] as $key => $value) {
                $items['items'][$key]['filename'] =
                    $this->storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    )."myDocument/".$value['filename'];
            }
        }
        return $items;
    }
}
