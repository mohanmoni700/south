<?php
declare (strict_types = 1);

namespace Alfakher\ExitB\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Vrpayecommerce\Vrpayecommerce\Controller\Payment\Response;
use Vrpayecommerce\VrpayecommerceGraphql\Helper\Payment;
use Vrpayecommerce\VrpayecommerceGraphql\Model\Resolver\GetPaymentStatus as getPaymentStatusResolver;
use Alfakher\ExitB\Model\ResourceModel\ExitbOrder\Collection;
use Alfakher\ExitB\Model\ExitbSync;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

class GetPaymentStatus
{
    public const TOPIC_NAME = 'exitb.massorder.sync';

    /**
     * @var Response
     */
    private Response $response;

    /**
     * @var Payment
     */
    private Payment $helperPayment;

    /**
     * Constructs
     *
     * @param Response $response
     * @param Payment $helperPayment
     * @param Collection $exitborderModel
     * @param ExitbSync $exitbsync
     * @param PublisherInterface $publisher
     * @param Json $json
     * @param OrderInterfaceFactory $orderFactory
     */
    public function __construct(
        Response $response,
        Payment $helperPayment,
        Collection $exitborderModel,
        ExitbSync $exitbsync,
        PublisherInterface $publisher,
        Json $json,
        OrderInterfaceFactory $orderFactory
    ) {
        $this->response = $response;
        $this->helperPayment = $helperPayment;
        $this->exitborderModel = $exitborderModel;
        $this->exitbsync = $exitbsync;
        $this->publisher = $publisher;
        $this->json = $json;
        $this->orderFactory = $orderFactory;
    }
    /**
     * Resolver
     *
     * @param getPaymentStatusResolver $subject
     * @param mixed $result
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     *
     * @return mixed
     */
    public function afterResolve(
        getPaymentStatusResolver $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        
        $customerId = $context->getUserId();
        $checkoutId = $args['input']['checkoutId'];
        $paymentMethod = $args['input']['paymentMethod'];
        $orderIncrementId = $args['input']['orderIncrementId'];

        $orderObj=$this->getOrderByIncerementId($orderIncrementId);
        $exitborderId = [
                "orderId" => (int) $orderObj->getId(),
        ];
        $websiteId = $orderObj->getStore()->getWebsiteId();
        $status = $this->exitborderModel->addFieldToFilter('order_id', $orderObj->getId());
        $collection = $status->getColumnValues('sync_status');
        $status_collection = !empty($collection) ? $collection[0] : null;
        if ($this->exitbsync->isModuleEnabled($websiteId) && $status_collection !== '1') {
                $this->publisher->publish(
                    self::TOPIC_NAME,
                    $this->json->serialize($exitborderId)
                );
        }
        return $result;
    }

    /**
     * Get an order based on increment id
     *
     * @param  int $incrementId
     * @return object
     */
    public function getOrderByIncerementId($incrementId)
    {
        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }
}
