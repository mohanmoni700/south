<?php
declare(strict_types=1);

namespace Alfakher\RequestQuote\Controller\Cart;

use Amasty\RequestQuote\Helper\Cart;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Email\Sender;
use Amasty\RequestQuote\Model\Quote\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Amasty\RequestQuote\Model\Customer\Manager;
use Amasty\RequestQuote\Model\Email\AdminNotification;
use Amasty\RequestQuote\Model\HidePrice\Provider as HidePriceProvider;
use Amasty\RequestQuote\Model\Registry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface as CartRepository;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Amasty\RequestQuote\Model\UrlResolver;
use Magento\Framework\Filter\LocalizedToNormalized;

class UpdatePost extends \Amasty\RequestQuote\Controller\Cart\UpdatePost
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var Sender
     */
    protected $emailSender;

    /**
     * @var SessionFactory
     */
    private SessionFactory $customerSessionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Data
     */
    private Data $configHelper;

    /**
     * @var Manager
     */
    private Manager $accountManagement;

    /**
     * @var CustomerUrl
     */
    private CustomerUrl $customerUrl;

    /**
     * @var AuthenticationInterface
     */
    private AuthenticationInterface $authentication;

    /**
     * @var PhpCookieManager
     */
    private PhpCookieManager $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var AdminNotification
     */
    private AdminNotification $adminNotification;

    /**
     * @var HidePriceProvider
     */
    private HidePriceProvider $hidePriceProvider;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @var CustomerExtractor
     */
    private CustomerExtractor $customerExtractor;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CartRepository
     */
    protected CartRepository $cartRepository;

    /**
     * @var CheckoutSessionFactory
     */
    protected CheckoutSessionFactory $checkoutSessionFactory;

    /**
     * @var UrlResolver
     */
    protected $urlResolver;

    /**
     * @var LocalizedToNormalized
     */
    private $localizedToNormalized;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param \Amasty\RequestQuote\Model\Cart $cart
     * @param ResolverInterface $localeResolver
     * @param PageFactory $resultPageFactory
     * @param EncoderInterface $encoder
     * @param Cart $cartHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param Sender $emailSender
     * @param SessionFactory $customerSessionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $configHelper
     * @param AdminNotification $adminNotification
     * @param AccountManagementInterface $accountManagement
     * @param CustomerUrl $customerUrl
     * @param AuthenticationInterface $authentication
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieManager
     * @param HidePriceProvider $hidePriceProvider
     * @param TimezoneInterface $timezone
     * @param CustomerExtractor $customerExtractor
     * @param LoggerInterface $logger
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param CartRepository $cartRepository
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param UrlResolver $urlResolver
     * @param LocalizedToNormalized $localizedToNormalized
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        \Amasty\RequestQuote\Model\Cart $cart,
        ResolverInterface $localeResolver,
        PageFactory $resultPageFactory,
        EncoderInterface $encoder,
        Cart $cartHelper,
        DataObjectFactory $dataObjectFactory,
        Sender $emailSender,
        SessionFactory $customerSessionFactory,
        PriceCurrencyInterface $priceCurrency,
        Data $configHelper,
        AdminNotification $adminNotification,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        AuthenticationInterface $authentication,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager,
        HidePriceProvider $hidePriceProvider,
        TimezoneInterface $timezone,
        CustomerExtractor $customerExtractor,
        LoggerInterface $logger,
        Registry $registry,
        DateTime $dateTime,
        CartRepository $cartRepository,
        CheckoutSessionFactory $checkoutSessionFactory,
        UrlResolver $urlResolver,
        LocalizedToNormalized $localizedToNormalized = null
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $localeResolver,
            $resultPageFactory,
            $encoder,
            $cartHelper,
            $dataObjectFactory,
            $emailSender,
            $customerSessionFactory,
            $priceCurrency,
            $configHelper,
            $adminNotification,
            $accountManagement,
            $customerUrl,
            $authentication,
            $cookieMetadataFactory,
            $cookieManager,
            $hidePriceProvider,
            $timezone,
            $customerExtractor,
            $logger,
            $registry,
            $dateTime,
            $urlResolver,
            $localizedToNormalized
        );
    }

    /**
     * Execute Method
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $backUrl = null;

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                /*start add to cart back code*/
                $session = $this->checkoutSessionFactory->create();
                $quote = $session->getQuote();
                $cartData = $this->getRequest()->getParam('cart');
                foreach ($cartData as $index => &$data) {
                    $quoteItem = $this->getCheckoutSession()->getQuote()->getItemById($index);
                    $qty = $data['qty'] ?? 1;
                    $quote->addProduct($quoteItem->getProduct(), $qty);
                }
                $this->cartRepository->save($quote);
                $this->checkoutSessionFactory->create()->getQuote()->collectTotals()->save();
                $backUrl = $this->_url->getUrl('checkout/cart');
                /*end add to cart back code*/
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            case 'submit':
                if ($this->_updateShoppingCart()) {
                    if (($email = $this->getRequest()->getParam('email', null))
                        && !$this->getConfigHelper()->isLoggedIn()
                    ) {
                        try {
                            $this->login();
                        } catch (LocalizedException $e) {
                            $this->messageManager->addErrorMessage($e->getMessage());
                            break;
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(__('Something went wrong'));
                            $this->getLogger()->error($e->getMessage());
                            break;
                        }
                    }
                    try {
                        $this->submitAction();
                    } catch (\Magento\Framework\Validator\Exception $exception) {
                        $this->messageManager->addErrorMessage($exception->getMessage());
                        return $this->_goBack($this->_redirect->getRefererUrl());
                    }
                    $backUrl = $this->_url->getUrl('amasty_quote/quote/success');
                }
                break;
            default:
                $this->_updateShoppingCart();
        }

        return $this->_goBack($backUrl);
    }
}
