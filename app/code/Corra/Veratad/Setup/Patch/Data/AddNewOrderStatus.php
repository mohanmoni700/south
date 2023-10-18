<?php
/**
 * @author  CORRA
 */
declare(strict_types=1);

namespace Corra\Veratad\Setup\Patch\Data;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

/**
 * Add new order status for processing state
 */
class AddNewOrderStatus implements DataPatchInterface
{
    /**
     * Custom Processing Order-Status code
     */
    private const ORDER_STATUS_PROCESSING_AGE_VERIFICATION_FAILED_CODE = 'age_verification_failed';

    /**
     * Custom Processing Order-Status label
     */
    private const ORDER_STATUS_PROCESSING_AGE_VERIFICATION_FAILED_LABEL = 'Age Verification Failed';

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
            'status' => self::ORDER_STATUS_PROCESSING_AGE_VERIFICATION_FAILED_CODE,
            'label' => self::ORDER_STATUS_PROCESSING_AGE_VERIFICATION_FAILED_LABEL,
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
    public function getAliases()
    {
        return [];
    }
}
