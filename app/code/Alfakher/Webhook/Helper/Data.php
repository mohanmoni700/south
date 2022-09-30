<?php

namespace Alfakher\Webhook\Helper;

use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Rma\Api\RmaAttributesManagementInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Webhook\Block\Adminhtml\LiquidFilters;
use Mageplaza\Webhook\Model\Config\Source\HookType;
use Mageplaza\Webhook\Model\Config\Source\Status;
use Mageplaza\Webhook\Model\HistoryFactory;
use Mageplaza\Webhook\Model\HookFactory;
use Mageplaza\Webhook\Model\ResourceModel\Hook\Collection;

class Data extends \Mageplaza\Webhook\Helper\Data
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    /**
     * @var Config
     */
    protected $eavConfig;
    /**
     * Data constructor
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $backendUrl
     * @param TransportBuilder $transportBuilder
     * @param CurlFactory $curlFactory
     * @param LiquidFilters $liquidFilters
     * @param HookFactory $hookFactory
     * @param HistoryFactory $historyFactory
     * @param CustomerRepositoryInterface $customer
     * @param State $state
     * @param Config $eavConfig
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
        State $state,
        Config $eavConfig
    ) {
        $this->state = $state;
        $this->eavConfig = $eavConfig;
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
    public function send($item, $hookType)
    {
        if (!$this->isEnabled()) {
            return;
        }

        /** @var Collection $hookCollection */
        $hookCollection = $this->hookFactory->create()->getCollection()
            ->addFieldToFilter('hook_type', $hookType)
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('store_ids', [
                ['finset' => Store::DEFAULT_STORE_ID],
                ['finset' => $this->getItemStore($item)],
            ])
            ->setOrder('priority', 'ASC');
        $isSendMail = $this->getConfigGeneral('alert_enabled');
        $sendTo = explode(',', $this->getConfigGeneral('send_to'));
        foreach ($hookCollection as $hook) {
            if ($hook->getHookType() === HookType::ORDER) {
                $statusItem = $item->getStatus();
                $orderStatus = explode(',', $hook->getOrderStatus());
                if (!in_array($statusItem, $orderStatus, true)) {
                    continue;
                }
            }
            $history = $this->historyFactory->create();
            if ($hookType == "update_document" || $hookType == "new_document") {
                $documentData = $this->AddFilePath($item, $hookType);
                $docItems = $this->addDocumentStatus($documentData, $hookType);
                $itemData = new DataObject($docItems);
            } elseif ($hookType == "delete_document") {
                $documentData = $this->AddFilePath($item, $hookType);
                $itemData = new DataObject($documentData['items']);
            } elseif ($hookType == "update_order") {
                $itemData = $item['item'];
            } elseif ($hookType == "create_rma" || $hookType == "update_rma") {
                $rmaItem = $item['item'];
                $rmaItemdata = [];
                foreach ($rmaItem->getItems() as $item) {
                    $rmaItemdataObj = new \Magento\Framework\DataObject();
                    $itemData = [];
                    $itemData = $item->getData();
                    $itemData['condition'] = $this->getRmaAttributeLabel('condition', $item->getCondition());
                    $itemData['reason'] = $this->getRmaAttributeLabel('reason', $item->getReason());
                    $itemData['resolution'] = $this->getRmaAttributeLabel('resolution', $item->getResolution());
                    $rmaItemdata[] = $rmaItemdataObj->setData($itemData);
                }
                $trackingNumbers = $rmaItem->getTrackingNumbers()->getData();
                $rmaItem->setItems($rmaItemdata);
                $rmaItem->setTrackingNumbers($trackingNumbers);
                $itemData = $rmaItem;
            } else {
                $itemData = $item;
            }

            $body = $this->generateLiquidTemplate($itemData, $hook->getBody());
            $data = [
                'hook_id' => $hook->getId(),
                'hook_name' => $hook->getName(),
                'store_ids' => $hook->getStoreIds(),
                'hook_type' => $hook->getHookType(),
                'priority' => $hook->getPriority(),
                'payload_url' => $this->generateLiquidTemplate($itemData, $hook->getPayloadUrl()),
                'body' => $this->generateLiquidTemplate($itemData, $hook->getBody()),
            ];
            $history->addData($data);
            try {
                $result = $this->sendHttpRequestFromHook($hook, $itemData);
                $history->setResponse(isset($result['response']) ? $result['response'] : '');
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => $e->getMessage(),
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
            ) . "myDocument/" . $items['items']['filename'];
        } else {
            foreach ($items['items'] as $key => $value) {
                $items['items'][$key]['filename'] =
                $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . "myDocument/" . $value['filename'];
            }
        }
        return $items;
    }

    /**
     * Add document status
     *
     * @param array $docItems
     * @param string $hookType
     * @return array
     */
    public function addDocumentStatus($docItems, $hookType)
    {
        if ($hookType == "update_document") {
            foreach ($docItems['items'] as $dockey => $docItem) {
                $docItems['items'][$dockey]['pending_document'] = 0;
                $docItems['items'][$dockey]['rejected_document'] = 0;
                $docItems['items'][$dockey]['expired_document'] = 0;
                $docItems['items'][$dockey]['approved_document'] = 0;
                $todate = date("Y-m-d");
                $expiryDate = $docItem['expiry_date'];

                if (($expiryDate <= $todate && $expiryDate != "")) {
                    $docItems['items'][$dockey]['expired_document'] = 1;
                }
                if ($docItem['status'] == 0 && $docItem['message'] == null) {
                    $docItems['items'][$dockey]['pending_document'] = 1;
                } else {
                    if ($docItem['status'] == 0 && $docItem['message'] != null) {
                        $docItems['items'][$dockey]['rejected_document'] = 1;
                    } elseif ($docItem['status'] == 1 && $docItem['message'] == null) {
                        $docItems['items'][$dockey]['approved_document'] = 1;
                    }
                }
            }
        }
        return $docItems;
    }

    /**
     * Get RMA attribute option label
     *
     * @param string $attributeCode
     * @param string $value
     */
    public function getRmaAttributeLabel($attributeCode, $value)
    {
        $optionVal = '';
        $attribute = $this->eavConfig->getAttribute(RmaAttributesManagementInterface::ENTITY_TYPE, $attributeCode);
        $optionVal = $attribute->getSource()->getOptionText($value);

        return $optionVal;
    }
}
