<?php
declare(strict_types=1);

namespace HookahShisha\Customization\Helper;

use Magento\Contact\Model\ConfigInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;

/**
 * Contact base helper
 */
class UserData extends \Magento\Contact\Helper\Data
{
    public const XML_PATH_ENABLED = ConfigInterface::XML_PATH_ENABLED;

    /**
     * Customer
     *
     * @var Session $_customerSession
     */
    protected $_customerSession;

    /**
     * @var CustomerViewHelper $_customerViewHelper
     */
    protected $_customerViewHelper;

    /**
     * @var DataPersistorInterface $dataPersistor
     */
    private $dataPersistor;

    /**
     * @var array $postData
     */
    private $postData = null;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
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
         * @var CustomerInterface $customer
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
         * @var CustomerInterface $customer
         */
        $customer = $this->_customerSession->getCustomerDataObject();
        $lastname = $customer->getLastname();
        return $lastname;
    }
}
