<?php

namespace HookahShisha\Customization\Helper;

use Magento\Contact\Model\ConfigInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Contact base helper
 */
class UserData extends \Magento\Contact\Helper\Data
{
    public const XML_PATH_ENABLED = ConfigInterface::XML_PATH_ENABLED;

    /**
     * Customer
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $postData = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerViewHelper $customerViewHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerViewHelper = $customerViewHelper;
        parent::__construct($context, $customerSession, $customerViewHelper);
    }

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }

        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();
        $firstname = $customer->getFirstname();
        return $firstname;
    }

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();
        $lastname = $customer->getLastname();
        return $lastname;
    }
}
