<?php
declare(strict_types=1);

namespace HookahShisha\Order\Ui\Component\Listing\Columns;

use Exception;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Listing\Columns\Date;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Psr\Log\LoggerInterface;

class OrderDate extends Date
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;
    /**
     * @var Info
     */
    private Info $infoBlock;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param BooleanUtils $booleanUtils
     * @param OrderRepositoryInterface $orderRepository
     * @param Info $infoBlock
     * @param LoggerInterface $logger
     * @param array $components
     * @param array $data
     * @param ResolverInterface|null $localeResolver
     * @param DataBundle|null $dataBundle
     */
    public function __construct(
        ContextInterface         $context,
        UiComponentFactory       $uiComponentFactory,
        TimezoneInterface        $timezone,
        BooleanUtils             $booleanUtils,
        OrderRepositoryInterface $orderRepository,
        Info $infoBlock,
        LoggerInterface $logger,
        array $components = [],
        array $data = [],
        ResolverInterface $localeResolver = null,
        DataBundle $dataBundle = null
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $timezone,
            $booleanUtils,
            $components,
            $data,
            $localeResolver,
            $dataBundle
        );
        $this->timezone       = $timezone;
        $this->orderRepository = $orderRepository;
        $this->infoBlock = $infoBlock;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $timezone = $this->timezone->getConfigTimezone(
                        ScopeInterface::SCOPE_STORE,
                        $this->getOrderStoreByOrderId((int)$item['entity_id'])
                    );
                    $formattedTime =  $this->infoBlock->formatDate(
                        $item[$this->getData('name')],
                        \IntlDateFormatter::MEDIUM,
                        true,
                        $timezone
                    );
                    $item[$this->getData('name')] = $formattedTime;
                }
            }
        }
        return $dataSource;
    }

    /**
     * Returns store id for order by order entity id
     *
     * @param int $orderId
     * @return int|null
     */
    private function getOrderStoreByOrderId(int $orderId): ?int
    {
        try {
            $order = $this->orderRepository->get($orderId);
            if ($order && $order->getEntityId()) {
                return (int)$order->getStoreId();
            }
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return 0;
    }
}
