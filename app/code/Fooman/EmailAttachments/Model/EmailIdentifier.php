<?php

declare(strict_types=1);

namespace Fooman\EmailAttachments\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Store\Model\ScopeInterface;

class EmailIdentifier
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EmailTypeFactory
     */
    private $emailTypeFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EmailTypeFactory $emailTypeFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EmailTypeFactory $emailTypeFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->emailTypeFactory = $emailTypeFactory;
    }

    /**
     * If you want to identify additional email types add an afterGetType plugin to this method.
     *
     * @param NextEmailInfo $nextEmailInfo
     * @return EmailType
     */
    public function getType(NextEmailInfo $nextEmailInfo)
    {
        $type = false;
        $templateVars = $nextEmailInfo->getTemplateVars();

        $varCode = $this->getMainEmailType($templateVars);
        if ($varCode) {
            $method = 'get' . ucfirst($varCode) . 'Email';
            $type = $this->$method(
                $nextEmailInfo->getTemplateIdentifier(),
                $templateVars[$varCode]->getStoreId()
            );
        }

        return $this->emailTypeFactory->create(['type' => $type, 'varCode' => $varCode]);
    }

    /**
     * Get main email type
     *
     * @param array $templateVars
     * @return false|string
     */
    private function getMainEmailType($templateVars)
    {
        if (isset($templateVars['shipment']) && method_exists($templateVars['shipment'], 'getStoreId')) {
            return 'shipment';
        }

        if (isset($templateVars['invoice']) && method_exists($templateVars['invoice'], 'getStoreId')) {
            return 'invoice';
        }

        if (isset($templateVars['creditmemo']) && method_exists($templateVars['creditmemo'], 'getStoreId')) {
            return 'creditmemo';
        }

        if (isset($templateVars['order']) && method_exists($templateVars['order'], 'getStoreId')) {
            return 'order';
        }

        //Not an email we can identify
        return false;
    }

    /**
     * Get shipment email
     *
     * @param string $templateIdentifier
     * @param int $storeId
     * @return false|string
     */
    private function getShipmentEmail($templateIdentifier, $storeId)
    {
        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            ShipmentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            ShipmentIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'shipment';
        }

        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            ShipmentCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            ShipmentCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'shipment_comment';
        }

        return false;
    }

    /**
     * Is template id match config
     *
     * @param string $templateIdentifier
     * @param string $guestTemplateConfigPath
     * @param string $customerTemplateConfigPath
     * @param int $storeId
     * @return bool
     */
    private function doesTemplateIdMatchConfig(
        $templateIdentifier,
        $guestTemplateConfigPath,
        $customerTemplateConfigPath,
        $storeId
    ) {
        return $this->scopeConfig->getValue($guestTemplateConfigPath, ScopeInterface::SCOPE_STORE, $storeId)
            === $templateIdentifier
            || $this->scopeConfig->getValue($customerTemplateConfigPath, ScopeInterface::SCOPE_STORE, $storeId)
            === $templateIdentifier;
    }

    /**
     * Get invoice email
     *
     * @param string $templateIdentifier
     * @param int $storeId
     * @return false|string
     */
    private function getInvoiceEmail($templateIdentifier, $storeId)
    {
        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            InvoiceIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            InvoiceIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'invoice';
        }

        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            InvoiceCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            InvoiceCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'invoice_comment';
        }

        return false;
    }

    /**
     * Get order email
     *
     * @param string $templateIdentifier
     * @param int $storeId
     * @return false|string
     */
    private function getOrderEmail($templateIdentifier, $storeId)
    {
        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            OrderIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            OrderIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'order';
        }

        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            OrderCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            OrderCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'order_comment';
        }

        return false;
    }

    /**
     * Get credit memo email
     *
     * @param string $templateIdentifier
     * @param int $storeId
     * @return false|string
     */
    private function getCreditmemoEmail($templateIdentifier, $storeId)
    {
        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            CreditmemoIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            CreditmemoIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'creditmemo';
        }

        if ($this->doesTemplateIdMatchConfig(
            $templateIdentifier,
            CreditmemoCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            CreditmemoCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            $storeId
        )) {
            return 'creditmemo_comment';
        }

        return false;
    }
}
