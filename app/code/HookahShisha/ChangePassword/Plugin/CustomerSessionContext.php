<?php
namespace HookahShisha\ChangePassword\Plugin;

class CustomerSessionContext
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory
     */
    protected $collection;
    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory $collection,
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory $collection
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->collection = $collection;
    }

    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->httpContext->setValue(
            'customer_id',
            $this->customerSession->getCustomerId(),
            false
        );
        $this->httpContext->setValue(
            'customer_name',
            $this->customerSession->getCustomer()->getName(),
            false
        );
        $this->httpContext->setValue(
            'firstname',
            $this->customerSession->getCustomer()->getFirstname(),
            false
        );
        $this->httpContext->setValue(
            'lastname',
            $this->customerSession->getCustomer()->getLastname(),
            false
        );
        $this->httpContext->setValue(
            'customer_email',
            $this->customerSession->getCustomer()->getEmail(),
            false
        );
        $this->httpContext->setValue(
            'customer_email',
            $this->customerSession->getCustomer()->getEmail(),
            false
        );
        $this->httpContext->setValue(
            'document_status',
            $this->getStatus(),
            false
        );
        $this->httpContext->setValue(
            'document_expiry_date',
            $this->getExpiryDate(),
            false
        );

        return $proceed($request);
    }
    public function getMydocuments()
    {
        $customer_id = $this->customerSession->getCustomerId();
        return $this->collection->create()
            ->addFieldToFilter('customer_id', ['eq' => $customer_id])
            ->addFieldToFilter('status', ['eq' => 0]);
    }
    public function getStatus()
    {
        $msg = 2;
        $doc_collection = $this->getMydocuments();
        foreach ($doc_collection as $value) {
            if (empty($value->getMessage())) {
                $msg = 0;
            } else {
                $msg = 1;
            }
        }
        return $msg;
    }
    public function getExpiryDate()
    {
        $msg = 1;
        $doc_collection = $this->getMydocuments();
        $todate = date("Y-m-d");
        foreach ($doc_collection as $value) {
            $expiry_date = $value->getExpiryDate();
            if ($expiry_date < $todate) {
                $msg = 0;
            }
        }
        return $msg;
    }
}
