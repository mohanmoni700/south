<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Cron;

use Alfakher\ExitB\Model\ExitbSync;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Alfakher\ExitB\Logger\Logger;

class CancelOrders
{
    public const WEBSITE_CODE = 'cancel_order/autocancel/Websiteid';
    public const CANCEL_ENABLE = 'cancel_order/autocancel/enabled';
    public const DAYS = 'cancel_order/autocancel/days';
    public const STATUS = 'pending';
    public const START_TIME = '00:00:00';
    public const END_TIME = '23:59:59';
    public const COMMENT = 'Sales Approved';

    /**
     * @var ExitbSync
     */
    protected $exitbsync;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ExitbSync $exitbsync
     * @param CollectionFactory $orderCollectionFactory
     * @param TimezoneInterface $date
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param Logger $logger
     */
    public function __construct(
        ExitbSync $exitbsync,
        CollectionFactory $orderCollectionFactory,
        TimezoneInterface $date,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        Logger $logger
    ) {
        $this->exitbsync = $exitbsync;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->date = $date;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->logger = $logger;
    }

    /**
     * Module enable
     *
     * @param int $websiteId
     * @return boolean
     */
    public function isModuleEnabled($websiteId)
    {
        return (bool) $this->exitbsync->getConfigValue(self::CANCEL_ENABLE, $websiteId);
    }

    /**
     * Get store ids from the website
     *
     * @param int $websiteId
     * @return array
     */
    public function getStoreIds($websiteId)
    {
        $storeIds = [];
        try {
            $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $storeIds;
    }

    /**
     * Order Cancel
     *
     * @return void
     */
    public function execute()
    {
        $websiteConfig = $this->exitbsync->getConfigValue(self::WEBSITE_CODE);
        $websiteIds = $websiteConfig ? explode(',', $websiteConfig) : [];
        try {
            foreach ($websiteIds as $websiteId) {
                if ($this->isModuleEnabled($websiteId)) {

                    $store = $this->getStoreIds($websiteId);
                    $storeId = implode(',', $store);
                    
                    $daysBefore = $this->exitbsync->getConfigValue(self::DAYS, $websiteId);
                    $actualDate = $this->date->date()->modify("-" . $daysBefore . " days")->format('Y-m-d');

                    $orders = $this->orderCollectionFactory->create();
                    $orders->addFieldToFilter('store_id', ['in' => $storeId]);
                    $orders->addFieldToFilter('status', ['in' => self::STATUS]);
                    $orders->addFieldToFilter('created_at', ['gteq' => $actualDate." ".self::START_TIME]);
                    $orders->addFieldToFilter('created_at', ['lteq' => $actualDate." ".self::END_TIME]);

                    foreach ($orders->getItems() as $order) {
                        $history = $order->getStatusHistoryCollection()->addFieldToFilter(
                            'comment',
                            ['eq' => self::COMMENT]
                        )->load();
                        if (!$history->toArray()['totalRecords']) {
                            $this->logger->info("Order Id -->". $order->getEntityId().
                                    "Created date -->". $order->getCreatedAt());
                            $order->cancel();
                            $order->addStatusHistoryComment('Cancelorder Using Cron')->setIsCustomerNotified(false);
                            $order->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return [];
        }
    }
}
