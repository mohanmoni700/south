<?php
namespace Alfakher\MyDocument\Block\Index;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory;

class Document extends \Magento\Framework\View\Element\Template
{

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
     * @param CollectionFactory $collection
     * @param array $data = []
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $collection,
        array $data = []
    ) {
        $this->collection = $collection;
        $this->customerSession = $customerSession;
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

            if ($str_status == 0 && empty($str_msg)) {
                $verification = "Your details are under verification. You would receive an email once there is an update.";
                $message[] = ["pending", $verification];
            } else {
                $verification = "";
            }

            if (in_array(0, $status) && !empty($str_msg)) {
                $rejected = "Some of your document(s) has been rejected. Please update the same";
                $message[] = ["reject", $rejected];
            } else {
                $rejected = "";
            }

            $todate = date("Y-m-d");
            
            foreach ($document as $value) {
                $expiry_date = $value['expiry_date'];
                if ($expiry_date <= $todate && $value['status'] == 0) {
                    $msg[] = "expired";
                } else {
                    $msg[] = "not expired";
                }
            }

            if (!empty($expiry_date)) {
                if (in_array('expired', $msg)) {
                    $docexpired = "Some of the document(s) has been expired";
                } else {
                    $docexpired = "";
                }
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
        }
    }
}
