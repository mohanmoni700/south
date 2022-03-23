<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Controller\Oauth;

use Magento\Backend\Model\UrlInterface;
use QuickBooksOnline\API\DataService\DataService;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Webkul\MultiQuickbooksConnect\Helper\Data as QuickbooksHelperData;
use Webkul\MultiQuickbooksConnect\Logger\Logger;
use OAuth;

class Oauth2 extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    const OAUTH_REQUEST_URL = 'https://oauth.intuit.com/oauth/v1/get_request_token';
    const OAUTH_ACCESS_URL = 'https://oauth.intuit.com/oauth/v1/get_access_token';
    const OAUTH_AUTHORISE_URL = 'https://appcenter.intuit.com/Connect/Begin';

    const OAUTH2_AUTHORISE_URL= 'https://appcenter.intuit.com/connect/oauth2';
    const OAUTH2_ACCESS_URL ='https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\Data
     */
    private $quickbooksHelper;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Model\AccountFactory
     */
    private $quickbooksAccount;

    /**
     * @param \Magento\Framework\App\Action\Context $context,
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory,
     * @param EncryptorInterface $encryptor,
     * @param TimezoneInterface $dateTime,
     * @param WriterInterface $configWriter,
     * @param UrlInterface $urlBuilder,
     * @param QuickbooksHelperData $quickbooksHelper,
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MultiQuickbooksConnect\Model\AccountFactory $quickbooksAccount,
        EncryptorInterface $encryptor,
        TimezoneInterface $dateTime,
        WriterInterface $configWriter,
        UrlInterface $urlBuilder,
        QuickbooksHelperData $quickbooksHelper,
        Logger $logger
    ) {
        $this->resultFactory = $context->getResultFactory();
        $this->resultPageFactory = $resultPageFactory;
        $this->quickbooksAccount = $quickbooksAccount;
        $this->encryptor = $encryptor;
        $this->dateTime = $dateTime;
        $this->configWriter = $configWriter;
        $this->urlBuilder = $urlBuilder;
        $this->quickbooksHelper = $quickbooksHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $config = $this->quickbooksHelper->getQuickbooksConnectConfig();
        if (strlen($config['client_id']) < 5) {
            $this->logger->info("Set the client id in the quickbooks configuration");
        }
        $redirectUri = $this->urlBuilder->getBaseUrl().'multiquickbooksconnect/oauth/oauth2';
        if (!isset($data['code'])) {
            try {
                // step 2: send user to intuit to authorize
                $dataService = DataService::Configure([
                    'auth_mode' => 'oauth2',
                    'ClientID' => $config['client_id'],
                    'ClientSecret' => $config['client_secret'],
                    'RedirectURI' => $redirectUri,
                    'scope' => 'com.intuit.quickbooks.accounting',
                    'baseUrl' => $config['account_type']
                ]);
                $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();
                $location = $oauth2LoginHelper->getAuthorizationCodeURL();
                $strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes";
                $closeWin = "<script>window.close();</script>";
                $script = "<script>window.open('".$location."','_self', '".$strWindowFeatures."')</script>";
                $reloadWin = "<script>window.opener.location.reload();</script>";
                return $this->returnScript($script.$closeWin);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $reloadWin = "<script>window.opener.location.reload();</script>";
                $closeWin = "<script>window.close();</script>";
                return $this->returnScript($reloadWin.$closeWin);
            }
        } else {
            try {
                $dataService = DataService::Configure([
                    'auth_mode' => 'oauth2',
                    'ClientID' => $config['client_id'],
                    'ClientSecret' => $config['client_secret'],
                    'RedirectURI' => $redirectUri,
                    'scope' => 'com.intuit.quickbooks.accounting',
                    'baseUrl' => $config['account_type']
                ]);
                $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();
                $model = $this->quickbooksAccount->create()->getCollection()
                        ->addFieldToFilter('is_current', 1)
                        ->setPageSize(1)->getFirstItem();
                if ($model && $model->getId()) {
                    $accessTokenObj = $oauth2LoginHelper->exchangeAuthorizationCodeForToken(
                        $data['code'],
                        $model->getRealmId()
                    );
                    $accessTokenValue = $accessTokenObj->getAccessToken();
                    $refreshTokenValue = $accessTokenObj->getRefreshToken();
                    if ($accessTokenObj && $refreshTokenValue && $accessTokenValue) {
                        $gmtCurrectTime = $this->dateTime->convertConfigTimeToUtc($this->dateTime->date());
                        $accessTokenExpireOn = date(
                            'Y-m-d H:i:s',
                            strtotime('+'.(3570).' seconds', strtotime($gmtCurrectTime))
                        );
                        $refreshTokenExpireOn = date(
                            'Y-m-d H:i:s',
                            strtotime('+'.(8639970).' seconds', strtotime($gmtCurrectTime))
                        );
                        $accessTokenValue = $this->encryptor->encrypt($accessTokenValue);
                        $refreshTokenValue = $this->encryptor->encrypt($refreshTokenValue);
                        $realmId = $data['realmId'];
                        $saveData = [
                            'oauth2_access_token' => $accessTokenValue,
                            'oauth2_refresh_token' => $refreshTokenValue,
                            'realm_id' => $realmId,
                            'oauth2_access_token_expire_on' => $accessTokenExpireOn,
                            'oauth2_refresh_token_expire_on' => $refreshTokenExpireOn,
                            'is_authenticated' => 1,
                        ];
                        $model->addData($saveData)->setId($model->getId())->save();

                        $msg  = 'Quickbooks account successfully authorized.'
                                        .' Please refresh page for view changes.';
                        $closeWin = "<script>alert('".__($msg)."');window.close();</script>";
                        return $this->returnScript($closeWin);
                    }
                }
                $closeWin = "<script>alert('".__('Quickbooks account did not authorize.')
                                                ."');window.close();</script>";
                $reloadWin = "<script>window.opener.location.reload();</script>";
                return $this->returnScript($closeWin.$reloadWin.$reloadWin);
            } catch (\Exception $e) {
                $this->logger->addError('Oauth2 validation - '.$e->getMessage());
                $closeWin = "<script>window.close();</script>";
                $reloadWin = "<script>window.opener.location.reload();</script>";
                $this->messageManager->addError($e->getMessage());
                return $this->returnScript($closeWin.$reloadWin.$reloadWin);
            }
        }
    }

    /**
     * returnScript
     * @param string $script
     * @return $resultPage
     */
    private function returnScript($script)
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultPage->setHeader('Content-Type', 'text/html')->setContents($script);
        return $resultPage;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
