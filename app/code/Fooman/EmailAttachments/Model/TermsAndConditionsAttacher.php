<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

use Fooman\EmailAttachments\Model\Api\AttachmentContainerInterface as ContainerInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;

class TermsAndConditionsAttacher
{
    /**
     * @var CollectionFactory
     */
    private $termsCollection;
    /**
     * @var ContentAttacher
     */
    private $contentAttacher;

    /**
     * @param CollectionFactory $termsCollection
     * @param ContentAttacher $contentAttacher
     */
    public function __construct(
        CollectionFactory $termsCollection,
        ContentAttacher $contentAttacher
    ) {
        $this->termsCollection = $termsCollection;
        $this->contentAttacher = $contentAttacher;
    }

    /**
     * Method to attach for store
     *
     * @param int $storeId
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function attachForStore($storeId, ContainerInterface $attachmentContainer)
    {
        /**
         * @var \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection $agreements
         */
        $agreements = $this->termsCollection->create();
        $agreements->addStoreFilter($storeId)->addFieldToFilter('is_active', 1);

        foreach ($agreements as $agreement) {
            $this->attachAgreement($agreement, $attachmentContainer);
        }
    }

    /**
     * Method to attach agreement
     *
     * @param AgreementInterface $agreement
     * @param ContainerInterface $attachmentContainer
     * @return void
     */
    public function attachAgreement(AgreementInterface $agreement, ContainerInterface $attachmentContainer)
    {
        if ($agreement->getIsHtml()) {
            $this->contentAttacher->addHtml(
                $this->buildHtmlAgreement($agreement),
                $agreement->getName() . '.html',
                $attachmentContainer
            );
        } else {
            $this->contentAttacher->addText(
                $agreement->getContent(),
                $agreement->getName() . '.txt',
                $attachmentContainer
            );
        }
    }

    /**
     * Method to build html agreement
     *
     * @param AgreementInterface $agreement
     * @return string
     */
    private function buildHtmlAgreement(AgreementInterface $agreement)
    {
        return sprintf(
            '<html>
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>%s</title>
                </head>
                <body>%s</body>
            </html>',
            $agreement->getName(),
            $agreement->getContent()
        );
    }
}
