<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model\Queue;

use Magento\Framework\MessageQueue\ConsumerConfiguration;
use Magento\Sales\Api\OrderRepositoryInterface;
use Alfakher\ExitB\Model\ExitbSync;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;
use Alfakher\ExitB\Model\OrderTokenFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Exitb Order
 */
class Consumer extends ConsumerConfiguration
{
    public const TOPIC_NAME = "exitb.massorder.sync";

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ExitbSync
     */
    protected $exitbsync;
    
    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * Check construct
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ExitbSync $exitbsync
     * @param Json $json
     * @param ManagerInterface $messageManager
     * @param OrderTokenFactory $ordertokenFactory
     * @param TimezoneInterface $date
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ExitbSync $exitbsync,
        Json $json,
        ManagerInterface $messageManager,
        OrderTokenFactory $ordertokenFactory,
        TimezoneInterface $date
    ) {
        $this->order = $orderRepository;
        $this->exitbsync = $exitbsync;
        $this->json = $json;
        $this->messageManager = $messageManager;
        $this->ordertokenFactory = $ordertokenFactory;
        $this->date = $date;
    }

    /**
     * Consumer process start
     *
     * @param mixed $request
     * @return void
     */
    public function process($request)
    {
        try {
            $data = $this->json->unserialize($request, true);
            $order = $this->order->get($data['orderId']);
            $websiteId = $order->getStore()->getWebsiteId();
            $tokenValue = $this->tokenCheck($websiteId);
            $this->exitbsync->orderSync($data['orderId'], $tokenValue);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Token check
     *
     * @param int $websiteId
     * @return mixed
     */
    public function tokenCheck($websiteId)
    {
        $tokenModel = $this->ordertokenFactory->create()->getCollection();
        $tokenModel->setOrder('entity_id', 'DESC')->setPageSize(1);

        $generateToken = true;
        if (!empty($tokenModel) && count($tokenModel) > 0) {
            foreach ($tokenModel as $tokenData) {
                $currentDate = $this->date->date()->format('Y-m-d H:i:s');
                $tokenDate = $tokenData['created_at'];
                $hour = abs(strtotime($tokenDate) - strtotime($currentDate))/(60*60);
                if ($hour > 10) {
                    $generateToken = true;
                } else {
                    $generateToken = false;
                }
            }
        }
        if ($generateToken == true) {
            $token_value = $this->exitbsync->tokenAuthentication($websiteId);
            $saveModel = $this->ordertokenFactory->create();
            $saveModel->setData('token', $token_value);
            $saveModel->save();
        }

        $model = $this->ordertokenFactory->create()->getCollection();
        $model->addFieldToSelect('token')->setOrder('entity_id', 'DESC')->setPageSize(1);
        foreach ($model as $token) {
            $token = $token['token'];
        }
        return $token;
    }
}
