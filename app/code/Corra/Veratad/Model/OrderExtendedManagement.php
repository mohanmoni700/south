<?php
namespace Corra\Veratad\Model;

use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Corra\Veratad\Model\OrderExtendedFactory as OrderExtendedFactory;
use Corra\Veratad\Model\ResourceModel\OrderExtendedFactory as OrderExtendedResourceFactory;
use Magento\Sales\Api\Data\OrderInterface;

class OrderExtendedManagement
{
    /**
     * @var OrderExtendedFactory
     */
    protected $orderExtendedFactory;

    /**
     * @var OrderExtendedResourceFactory
     */
    protected $orderExtendedResourceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderExtended InformationManagement constructor.
     *
     * @param \Corra\Veratad\Model\OrderExtendedFactory $orderExtendedFactory
     * @param OrderExtendedResourceFactory $orderExtendedResourceFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderExtendedFactory $orderExtendedFactory,
        OrderExtendedResourceFactory $orderExtendedResourceFactory,
        LoggerInterface $logger
    ) {
        $this->orderExtendedFactory = $orderExtendedFactory;
        $this->orderExtendedResourceFactory = $orderExtendedResourceFactory;
        $this->logger = $logger;
    }

    /**
     * Adding VratedResponse.
     *
     * @param OrderInterface $order
     * @param string $veratadDob
     * @param bool $isAgeVerified
     * @param string $billing
     * @param string $shipping
     * @param string $veratadDetail
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function addOrderExtendedInfo(
        $order,
        $veratadDob,
        $isAgeVerified,
        $billing,
        $shipping,
        $veratadDetail
    ) {
        try {
            if (!empty($order && $veratadDob)) {
                $orderExtendedModel = $this->orderExtendedFactory->create();
                $orderExtendedModel->load($order->getEntityId(), 'sales_order_id');
                if (count($orderExtendedModel->getData())) {
                    $orderExtendedModel->setEntityId($orderExtendedModel->getEntityId());
                }
                $orderExtendedModel->setSalesOrderId($order->getEntityId());
                $orderExtendedModel->setVeratadDob($veratadDob);
                $orderExtendedModel->setIsAgeVerified($isAgeVerified);
                $orderExtendedModel->setVeratadBillingAddressStatus($billing);
                $orderExtendedModel->setVeratadShippingAddressStatus($shipping);
                $orderExtendedModel->setVeratadDetail($veratadDetail);
                $this->orderExtendedResourceFactory->create()->save($orderExtendedModel);
            } else {
                throw new LocalizedException(
                    __("Can't save. Missing Verated DOB")
                );
            }
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $e;
        }
    }
}
