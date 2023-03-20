<?php
/**
 * @author digital@hookah.com
 */
declare(strict_types=1);

namespace HookahShisha\LoginAsCustomer\Controller\Adminhtml\Login;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerEnabledForCustomerInterface;
use Magento\LoginAsCustomerApi\Api\SaveAuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\SetLoggedAsCustomerCustomerIdInterface;
use Magento\LoginAsCustomerApi\Api\GenerateAuthenticationSecretInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\ManageStoreCookie;
use Magento\Integration\Model\Oauth\TokenFactory;

/**
 * Login as customer action
 * Generate secret key and forward to the storefront action
 * Pwa support
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_LoginAsCustomer::login';

    public const PWA_STORES = [
        'default',
        'hookah_company_store_view',
        'hookah_store_view',
        'ooka_de_store_de',
        'ooka_de_store_en',
        'ooka_usa_store_en',
        'ooka_uae_store_en',
        'ooka_uae_store_ar',
        'ooka_store_view'
    ];

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var AuthenticationDataInterfaceFactory
     */
    private $authenticationDataFactory;

    /**
     * @var SaveAuthenticationDataInterface
     */
    private $saveAuthenticationData;

    /**
     * @var DeleteAuthenticationDataForUserInterface
     */
    private $deleteAuthenticationDataForUser;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var Share
     */
    private $share;

    /**
     * @var ManageStoreCookie
     */
    private $manageStoreCookie;

    /**
     * @var SetLoggedAsCustomerCustomerIdInterface
     */
    private $setLoggedAsCustomerCustomerId;

    /**
     * @var IsLoginAsCustomerEnabledForCustomerInterface
     */
    private $isLoginAsCustomerEnabled;

    /**
     * @var GenerateAuthenticationSecretInterface
     */
    private $generateAuthenticationSecret;

    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigInterface $config
     * @param AuthenticationDataInterfaceFactory $authenticationDataFactory
     * @param SaveAuthenticationDataInterface $saveAuthenticationData
     * @param DeleteAuthenticationDataForUserInterface $deleteAuthenticationDataForUser
     * @param TokenFactory $tokenModelFactory
     * @param Url $url
     * @param Share|null $share
     * @param ManageStoreCookie|null $manageStoreCookie
     * @param SetLoggedAsCustomerCustomerIdInterface|null $setLoggedAsCustomerCustomerId
     * @param IsLoginAsCustomerEnabledForCustomerInterface|null $isLoginAsCustomerEnabled
     * @param GenerateAuthenticationSecretInterface|null $generateAuthenticationSecret
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context                                       $context,
        Session                                       $authSession,
        StoreManagerInterface                         $storeManager,
        CustomerRepositoryInterface                   $customerRepository,
        ConfigInterface                               $config,
        AuthenticationDataInterfaceFactory            $authenticationDataFactory,
        SaveAuthenticationDataInterface               $saveAuthenticationData,
        DeleteAuthenticationDataForUserInterface      $deleteAuthenticationDataForUser,
        TokenFactory                                  $tokenModelFactory,
        Url                                           $url,
        ?Share                                        $share = null,
        ?ManageStoreCookie                            $manageStoreCookie = null,
        ?SetLoggedAsCustomerCustomerIdInterface       $setLoggedAsCustomerCustomerId = null,
        ?IsLoginAsCustomerEnabledForCustomerInterface $isLoginAsCustomerEnabled = null,
        ?GenerateAuthenticationSecretInterface        $generateAuthenticationSecret = null
    ) {
        parent::__construct($context);

        $this->authSession = $authSession;
        $this->storeManager = $storeManager;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->authenticationDataFactory = $authenticationDataFactory;
        $this->saveAuthenticationData = $saveAuthenticationData;
        $this->deleteAuthenticationDataForUser = $deleteAuthenticationDataForUser;
        $this->url = $url;
        $this->share = $share ?? ObjectManager::getInstance()->get(Share::class);
        $this->manageStoreCookie = $manageStoreCookie ?? ObjectManager::getInstance()->get(ManageStoreCookie::class);
        $this->setLoggedAsCustomerCustomerId = $setLoggedAsCustomerCustomerId
            ?? ObjectManager::getInstance()->get(SetLoggedAsCustomerCustomerIdInterface::class);
        $this->isLoginAsCustomerEnabled = $isLoginAsCustomerEnabled
            ?? ObjectManager::getInstance()->get(IsLoginAsCustomerEnabledForCustomerInterface::class);
        $this->generateAuthenticationSecret = $generateAuthenticationSecret
            ?? ObjectManager::getInstance()->get(GenerateAuthenticationSecretInterface::class);
    }

    /**
     * Login as customer
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        $messages = [];

        $customerId = (int)$this->_request->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int)$this->_request->getParam('entity_id');
        }

        $isLoginAsCustomerEnabled = $this->isLoginAsCustomerEnabled->execute($customerId);
        if (!$isLoginAsCustomerEnabled->isEnabled()) {
            foreach ($isLoginAsCustomerEnabled->getMessages() as $message) {
                $messages[] = __($message);
            }

            return $this->prepareJsonResult($messages);
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            $messages[] = __('Customer with this ID no longer exists.');
            return $this->prepareJsonResult($messages);
        }

        if ($this->config->isStoreManualChoiceEnabled()) {
            $storeId = (int)$this->_request->getParam('store_id');
            if (empty($storeId)) {
                $messages[] = __('Please select a Store View to login in.');
                return $this->prepareJsonResult($messages);
            }
        } elseif ($this->share->isGlobalScope()) {
            $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
        } else {
            $storeId = (int)$customer->getStoreId();
        }

        $adminUser = $this->authSession->getUser();
        $userId = (int)$adminUser->getId();

        /** @var AuthenticationDataInterface $authenticationData */
        $authenticationData = $this->authenticationDataFactory->create(
            [
                'customerId' => $customerId,
                'adminId' => $userId,
                'extensionAttributes' => null,
            ]
        );

        $this->deleteAuthenticationDataForUser->execute($userId);
        $this->saveAuthenticationData->execute($authenticationData);
        $this->setLoggedAsCustomerCustomerId->execute($customerId);

        $secret = $this->generateAuthenticationSecret->execute($authenticationData);
        $redirectUrl = $this->getLoginProceedRedirectUrl($secret, $storeId, $customerId);

        return $this->prepareJsonResult($messages, $redirectUrl);
    }

    /**
     * Get login proceed redirect url
     *
     * @param string $secret
     * @param int $storeId
     * @param int $customerId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getLoginProceedRedirectUrl(string $secret, int $storeId, int $customerId): string
    {
        $targetStore = $this->storeManager->getStore($storeId);

        // Check weather it's a pwa store and return url with token
        if (in_array($targetStore->getCode(), self::PWA_STORES)) {
            $tokenModel = $this->tokenModelFactory->create();
            $customerToken = $tokenModel->createCustomerToken($customerId)->getToken();
            return $this->url
                ->setScope($targetStore)
                ->getUrl('login-as-customer', ['_query' => ['aToken' => $customerToken], '_nosid' => true]);
        }
        $queryParameters = ['secret' => $secret];
        $redirectUrl = $this->url
            ->setScope($targetStore)
            ->getUrl('loginascustomer/login/index', ['_query' => $queryParameters, '_nosid' => true]);

        if (!$targetStore->isUseStoreInUrl()) {
            $fromStore = $this->storeManager->getStore();
            $redirectUrl = $this->manageStoreCookie->switch($fromStore, $targetStore, $redirectUrl);
        }

        return $redirectUrl;
    }

    /**
     * Prepare JSON result
     *
     * @param array $messages
     * @param string|null $redirectUrl
     * @return JsonResult
     */
    private function prepareJsonResult(array $messages, ?string $redirectUrl = null)
    {
        /** @var JsonResult $jsonResult */
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $jsonResult->setData([
            'redirectUrl' => $redirectUrl,
            'messages' => $messages,
        ]);

        return $jsonResult;
    }
}
