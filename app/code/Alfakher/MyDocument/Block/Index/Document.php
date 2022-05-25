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
     * @var \HookahShisha\ChangePassword\Plugin\CustomerSessionContext
     */
    protected $CustomerSessionContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $collection
     * @param \HookahShisha\ChangePassword\Plugin\CustomerSessionContext $CustomerSessionContext
     * @param array $data = []
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CollectionFactory $collection,
        \HookahShisha\ChangePassword\Plugin\CustomerSessionContext $CustomerSessionContext,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->collection = $collection;
        $this->CustomerSessionContext = $CustomerSessionContext;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->CustomerSessionContext->getCustomer()->getId();
    }

    /**
     * @inheritDoc
     */
    public function getDocumentCollection($customerid)
    {
        return $this->collection->create()->addFieldToFilter('customer_id', ['eq' => $customerid]);
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

            if (in_array(0, $status) && !empty($str_msg)) {
                $rejected = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_document_rejection_sections', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $message[] = ["reject", $rejected];
            } else {
                $rejected = "";
            }

            $todate = date("Y-m-d");

            foreach ($document as $value) {
                $expiry_date = $value['expiry_date'];
                if (($expiry_date <= $todate && $expiry_date != "")) {
                    $msg[] = "expired";
                } else {
                    $msg[] = "not expired";
                }
            }

            if (in_array('expired', $msg) && !(in_array(0, $status) && empty($str_msg))) {
                $docexpired = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_documents_expired_sections', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            } else {
                $docexpired = "";
            }

            if ($docexpired != '') {

                $message[] = ["reject", $docexpired];
            }

            if (in_array(0, $status) && empty($str_msg) && !(in_array('expired', $msg))) {
                $verification = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_document_Verification_section', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $message[] = ["pending", $verification];
            } else {
                $verification = "";
            }
            if (in_array('expired', $msg) && in_array(0, $status) && empty($str_msg)) {
                $expunderveri = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_documents_underverification_and_expired', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $message[] = ["pending", $expunderveri];
            } else {
                $expunderveri = "";
            }

            return [
                'message' => $message,
                'pending' => $verification,
                'reject' => $rejected,
                'reject' => $docexpired,
                'pending' => $expunderveri,
            ];
        } else {
            $verification = $this->scopeConfig->getValue('hookahshisha/productpage/productpageb2b_documents_verification_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $message[] = ["pending", $verification];
            return [
                'message' => $message,
                'pending' => $verification,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function isCustomerLoggedIn()
    {
        if ($this->customerSession->isLoggedIn()) {
            return 'Yes';
        } else {
            return 'No';
        }
    }

    /**
     * @inheritDoc
     */
    public function isDocumentApproved()
    {
        if (!$this->CustomerSessionContext->getStatus()) {
            return 'Yes';
        } else {
            return 'No';
        }
    }
}
