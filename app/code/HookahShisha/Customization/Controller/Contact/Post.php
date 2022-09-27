<?php
 
namespace HookahShisha\Customization\Controller\Contact;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Post extends \Magento\Contact\Controller\Index\Post
{
    /**
     * Website Code
     */
    public const WEBSITE_CODE = 'hookahshisha/website_code_setting/website_code';

    /**
     * Scope Configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var MailInterface
     */
    private $mail;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger = null,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context, $contactsConfig, $mail, $dataPersistor, $logger);
        $this->context = $context;
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Validate function
     *
     * @return array
     * @throws \Exception
     */
    private function validatedParams()
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $configWebsite = $this->scopeConfig->getValue(self::WEBSITE_CODE, ScopeInterface::SCOPE_STORE);
        $websiteCodes = explode(',', $configWebsite);
        $request = $this->getRequest();

        // Server Side Validation - Custom Function for First Name And Last Name
        if (in_array($websiteCode, $websiteCodes)) {

            if (trim($request->getParam('first_name')) === '') {
                throw new LocalizedException(__('Enter the First Name and try again.'));
            }
            if (trim($request->getParam('last_name')) === '') {
                throw new LocalizedException(__('Enter the Last Name and try again.'));
            }
        } else {

            if (trim($request->getParam('name')) === '') {
                throw new LocalizedException(__('Enter the Name and try again.'));

            }
        }
        // Server Side Validation - End of Custom Function for First Name And Last Name
        
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new LocalizedException(__(''));
        }

        return $request->getParams();
    }
}
