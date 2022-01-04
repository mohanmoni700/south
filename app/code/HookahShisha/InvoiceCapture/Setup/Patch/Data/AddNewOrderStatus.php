<?php
/**
 * @author  CORRA
 */
declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Setup\Patch\Data;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

/**
 * Add new order status for processing state
 */
class AddNewOrderStatus implements DataPatchInterface, PatchVersionInterface
{
    /**
     * Custom Processing Order-Status code
     */
    private const ORDER_STATUS_SHIPPED_CODE = 'shipped';

    /**
     * Custom Processing Order-Status label
     */
    private const ORDER_STATUS_SHIPPED_LABEL = 'Shipped';

    /**
     * Status Factory Object
     *
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * Status Resource Factory Object
     *
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * InstallData constructor
     *
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->addNewOrderStatus();
    }

    /**
     * Add new processing status
     *
     * @throws \Exception
     */
    protected function addNewOrderStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::ORDER_STATUS_SHIPPED_CODE,
            'label' => self::ORDER_STATUS_SHIPPED_LABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }

        $status->assignState(Order::STATE_PROCESSING, false, true);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
