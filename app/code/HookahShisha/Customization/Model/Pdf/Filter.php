<?php
namespace HookahShisha\Customization\Model\Pdf;

class Filter
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * Filter constructor
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(\Magento\Sales\Api\OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    /**
     * Add company variables to data
     *
     * @param \Magetrend\PdfTemplates\Model\Pdf\Filter $subject
     * @param array $data
     * @param string $type
     * @return mixed
     */
    public function afteraddAdditionalData(\Magetrend\PdfTemplates\Model\Pdf\Filter $subject, $data, $type)
    {
        $order_id = $data['order_id'];
        $companyNames = $this->getCompanyNames($order_id);
        $data['business_company'] = $companyNames;
        return $data;
    }
    /**
     * Get company name from orderreposetory
     *
     * @param int $order_id
     * @return mixed
     */
    public function getCompanyNames($order_id)
    {
        $order = $this->orderRepository->get($order_id);
        $companyName = '';
        if ($order->getExtensionAttributes() !== null
            && $order->getExtensionAttributes()->getCompanyOrderAttributes() !== null
        ) {
            $companyName = $order->getExtensionAttributes()->getCompanyOrderAttributes()->getCompanyName();
        }
        return $companyName;
    }
}
