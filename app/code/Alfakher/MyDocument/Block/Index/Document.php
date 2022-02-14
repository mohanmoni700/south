<?php
namespace Alfakher\MyDocument\Block\Index;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;

class Document extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collection
     * @param array $data = []
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CollectionFactory $collection,
        array $data = []
    ) {
        $this->collection = $collection;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        $customerid = $this->customerSession->getCustomer()->getId();
        return $customerid;
    }

    /**
     * @inheritDoc
     */
    public function getDocumentCollection($customerid)
    {
        $collection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customerid]);
        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function getMessageData()
    {
        $customer_id = $this->customerSession->getCustomer()->getId();
        $doc_collection = $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customer_id]);
        $document = $doc_collection->getData();
        $dataSize = count($document);

        if ($dataSize != 0) {
            $status = [];
            $expiry_date = [];
            $rejectedmessage = [];

            foreach ($document as $value) {
                $status[] = $value['status'];
                $rejectedmessage[] = $value['message'];
            }

            $str_msg = implode("", $rejectedmessage);
            $str_status = implode(" ", $status);

            $message = [];

            if (in_array(0, $status) && empty($str_msg)) {
                $verification = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_document_Verification_section', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $message[] = ["pending", $verification];
            } else {
                $verification = "";
            }

            if (in_array(0, $status) && !empty($str_msg)) {
                $rejected = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_document_rejection_sections', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $message[] = ["reject", $rejected];
            } else {
                $rejected = "";
            }

            $todate = date("Y-m-d");

            foreach ($document as $value) {
                $expiry_date = $value['expiry_date'];
                if (($expiry_date <= $todate && $expiry_date != "")
                    && $value['status'] == 0) {
                    $msg[] = "expired";
                } else {
                    $msg[] = "not expired";
                }
            }

            if (in_array('expired', $msg)) {
                $docexpired = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_documents_expired_sections', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            } else {
                $docexpired = "";
            }

            if ($docexpired != '') {

                $message[] = ["reject", $docexpired];
            }
            return [
                'message' => $message,
                'pending' => $verification,
                'reject' => $rejected,
                'reject' => $docexpired,
            ];
        } else {
            $verification = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_documents_verification_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return [
                'message' => [
                    [
                        "pending",
                        $message,
                    ],
                ],
            ];
        }
    }
}
