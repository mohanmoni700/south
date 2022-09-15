<?php
declare (strict_types = 1);

namespace Alfakher\RmaCustomization\Controller\Returns;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rma\Api\Data\RmaInterface;
use Magento\Rma\Helper\Data;
use Magento\Rma\Model\Rma;
use Magento\Rma\Model\RmaFactory;
use Magento\Rma\Model\Rma\Source\Status;
use Magento\Rma\Model\Rma\Status\History;
use Magento\Rma\Model\Rma\Status\HistoryFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;

/**
 * Controller class Submit. Contains logic of request, responsible for return creation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Submit extends \Magento\Rma\Controller\Returns implements HttpPostActionInterface
{
    /**
     * @var RmaFactory
     */
    private $rmaModelFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var HistoryFactory
     */
    private $statusHistoryFactory;

    /**
     * @var Data
     */
    private $rmaHelper;

    /**
     * Submit constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param RmaFactory $rmaModelFactory
     * @param OrderRepository $orderRepository
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     * @param HistoryFactory $statusHistoryFactory
     * @param Data|null $rmaHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        RmaFactory $rmaModelFactory,
        OrderRepository $orderRepository,
        LoggerInterface $logger,
        DateTime $dateTime,
        HistoryFactory $statusHistoryFactory,
        ? Data $rmaHelper = null
    ) {
        $this->rmaModelFactory = $rmaModelFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->statusHistoryFactory = $statusHistoryFactory;
        parent::__construct($context, $coreRegistry);

        $this->rmaHelper = $rmaHelper ?: $this->_objectManager->create(Data::class);
    }

    /**
     * Goods return requests entrypoint
     */
    public function execute()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $post = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->rmaHelper->canCreateRma($orderId)) {
            return $resultRedirect->setPath('*/*/create', ['order_id' => $orderId]);
        }

        if ($post && !empty($post['items'])) {
            try {

                $custom_items = [];
                $custom_items['customer_custom_email'] = $post['customer_custom_email'];
                $i = 0;
                foreach ($post['rmaitems']['rma_item_id'] as $value) {
                    $custom_items['items'][$i] = ['order_item_id' => $post['rmaitems']['rma_item_id'][$i],
                        'qty_requested' => $post['rmaitems']['rma_qty'][$i],
                        'resolution' => $post['items'][0]['resolution'],
                        'condition' => $post['items'][0]['condition'],
                        'reason' => $post['items'][0]['reason']];
                    $i++;
                }
                $custom_items['rma_comment'] = $post['rma_comment'];
                $custom_items['form_key'] = $post['form_key'];
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderRepository->get($orderId);

                if (!$this->_canViewOrder($order)) {
                    return $resultRedirect->setPath('sales/order/history');
                }
                /** @var Rma $rmaObject */
                $rmaObject = $this->buildRma($order, $custom_items);
                if (!$rmaObject->saveRma($custom_items)) {
                    $url = $this->_url->getUrl('*/*/create', ['order_id' => $orderId]);
                    return $resultRedirect->setPath($url);

                }
                $statusHistory = $this->statusHistoryFactory->create();
                $statusHistory->setRmaEntityId($rmaObject->getEntityId());
                $statusHistory->sendNewRmaEmail();
                $statusHistory->saveSystemComment();

                if (isset($post['rma_comment']) && !empty($post['rma_comment'])) {
                    /** @var History $comment */
                    $comment = $this->statusHistoryFactory->create();
                    $comment->setRmaEntityId($rmaObject->getEntityId());
                    $comment->saveComment($post['rma_comment'], true, false);
                }

                $this->messageManager->addSuccessMessage(
                    __(
                        'You submitted Return #%1.',
                        $rmaObject->getIncrementId()
                    )
                );

                /* Start - New event added*/
                $this->_eventManager->dispatch(
                    'rma_create_after',
                    [
                        'item' => $rmaObject,
                    ]
                );
                /* end - New event added*/

                return $resultRedirect->setPath('rma/*/history');
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t create a return right now. Please try again later.')
                );
                $this->logger->critical($e->getMessage());
                return $resultRedirect->setPath('rma/*/history');
            }
        } else {
            return $resultRedirect->setPath('sales/order/history');
        }
    }

    /**
     * Triggers save order and create history comment process
     *
     * @param Order $order
     * @param array $post
     * @return RmaInterface
     */
    private function buildRma(Order $order, array $post): RmaInterface
    {
        /** @var RmaInterface $rmaModel */
        $rmaModel = $this->rmaModelFactory->create();

        $rmaModel->setData(
            [
                'status' => Status::STATE_PENDING,
                'date_requested' => $this->dateTime->gmtDate(),
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'store_id' => $order->getStoreId(),
                'customer_id' => $order->getCustomerId(),
                'order_date' => $order->getCreatedAt(),
                'customer_name' => $order->getCustomerName(),
                'customer_custom_email' => $post['customer_custom_email'],
            ]
        );
        return $rmaModel;
    }
}
