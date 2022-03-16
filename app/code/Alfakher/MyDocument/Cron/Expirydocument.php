<?php

namespace Alfakher\MyDocument\Cron;

use Alfakher\MyDocument\Model\ResourceModel\MyDocument\CollectionFactory as DocumentCollectionFactory;

class Expirydocument
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var DocumentCollectionFactory
     */
    protected $documentcollection;
    /**
     * @var documentHelper
     */
    protected $documentHelper;
    /**
     * @var documentRepository
     */
    protected $documentRepository;
    /**
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Alfakher\MyDocument\Helper\Data $documentHelper
     * @param \Alfakher\MyDocument\Model\MyDocumentRepository $documentRepository
     * @param DocumentCollectionFactory $documentcollection
     */
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Alfakher\MyDocument\Helper\Data $documentHelper,
        \Alfakher\MyDocument\Model\MyDocumentRepository $documentRepository,
        DocumentCollectionFactory $documentcollection
    ) {
        $this->logger = $loggerInterface;
        $this->documentHelper = $documentHelper;
        $this->documentRepository = $documentRepository;
        $this->documentcollection = $documentcollection;
    }

    /**
     * Perform expiry document mail
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {

        $documentArray = $this->getDocumentCollection();
        if ($documentArray) {

            foreach ($documentArray as $customerId => $document) {
                $documentName = '';
                $documentNameArray = array_column($document, 'docname');
                $documentIdArray = array_column($document, 'mydocument_id');
                $documentName = implode(",", array_column($document, 'docname'));

                $sendEmail = $this->documentHelper->sendExpiryMail($documentNameArray, $customerId);
                if ($sendEmail) {
                    $this->setExpiredEmailFlag($documentIdArray, $customerId);
                }
            }

        }
    }

    /**
     * Get document collection for expired document mail
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDocumentCollection()
    {
        $now = new \DateTime();
        $documentcollection = $this->documentcollection->create()
            ->addFieldToFilter('is_delete', ['eq' => 0])
            ->addFieldToFilter('expiry_date', ['lteq' => $now->format('Y-m-d H:i:s')])
            ->addFieldToFilter('notify_expire_doc_mail', ['eq' => 0]);

        $customerDocumentArray = [];
        foreach ($documentcollection as $documentArray) {
            $customerArray = [];
            $customerId = $documentArray->getCustomerId();
            $isDelete = $documentArray->getIsDelete();
            if ($customerId && !$isDelete) {
                $customerArray['mydocument_id'] = $documentArray->getMydocumentId();
                $customerArray['filename'] = $documentArray->getFilename();
                $customerArray['document_name'] = $documentArray->getDocumentName();
                if ($documentArray->getDocumentName()) {
                    $customerArray['docname'] = $documentArray->getDocumentName();
                } else {
                    $customerArray['docname'] = $documentArray->getFilename();
                }
                $customerDocumentArray[$customerId][] = $customerArray;
            }

        }
        return $customerDocumentArray;
    }
    /**
     * Set expired mail flag
     *
     * @param {array} $documentIdArray
     * @param {int} $customerId
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function setExpiredEmailFlag($documentIdArray, $customerId)
    {
        foreach ($documentIdArray as $documentId) {

            $document = $this->documentRepository->get($documentId);
            if ($document->getCustomerId() == $customerId) {
                $document->setNotifyExpireDocMail(1);
                $this->documentRepository->save($document);
            }
        }
    }
}
