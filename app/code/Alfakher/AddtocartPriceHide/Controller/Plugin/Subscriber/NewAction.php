<?php
namespace Alfakher\AddtocartPriceHide\Controller\Plugin\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Phrase;

/**
 * New newsletter subscription action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;
    /**
     * @var resultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param EmailValidator $emailValidator = null
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Magento\Newsletter\Controller\Subscriber\NewAction $messageinfo
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        SubscriptionManagerInterface $subscriptionManager,
        EmailValidator $emailValidator = null,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Newsletter\Controller\Subscriber\NewAction $messageinfo,
        ResultFactory $resultFactory
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->subscriptionManager = $subscriptionManager;
        $this->emailValidator = $emailValidator ?: ObjectManager::getInstance()->get(EmailValidator::class);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageinfo = $messageinfo;
        $this->resultFactory = $resultFactory;
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customerAccountManagement,
            $subscriptionManager,
        );
    }

    /**
     * Retrieve available Order fields list
     *
     * @param string $subject
     * @param string $procede
     * @return array
     */
    public function aroundExecute($subject, $procede)
    {
        $response = [];
        $websiteId = (int) $this->_storeManager->getStore()->getWebsiteId();

        $storeCodeId = $this->_storeManager->getWebsite()->getCode();
        if ($storeCodeId == 'shisha_world_b2b') {
            return $this->informactionMessage();
        } else {
            if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
                $email = (string) $this->getRequest()->getPost('email');
                try {
                    $this->validateEmailFormat($email);
                    $this->validateGuestSubscription();
                    $this->validateEmailAvailable($email);

                    $websiteId = (int) $this->_storeManager->getStore()->getWebsiteId();
                    /** @var Subscriber $subscriber */
                    $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
                    if ($subscriber->getId()
                    && (int) $subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
                        throw new LocalizedException(
                            __('This email address is already subscribed.')
                        );
                    }

                    $status = $this->_subscriberFactory->create()->subscribe($email);

                    if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                        $response = [
                        'status' => 'OK',
                        'msg' => 'The confirmation request has been sent.',
                        ];
                    } else {
                        $response = [
                        'status' => 'OK',
                        'msg' => 'Thank you for your subscription.',
                        ];
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $response = [
                    'status' => 'ERROR',
                    'msg' => __($e->getMessage()),
                    ];
                } catch (\Exception $e) {
                    $response = [
                    'status' => 'ERROR',
                    'msg' => __('Something went wrong with the subscription.'),
                    ];
                }
            }

            return $this->resultJsonFactory->create()->setData($response);
        }
    }
     /**
      * New subscription action
      *
      * @return Redirect
      */
    public function informactionMessage()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
                /** @var Subscriber $subscriber */
                $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
                if ($subscriber->getId()
                    && (int)$subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
                    throw new LocalizedException(
                        __('This email address is already subscribed.')
                    );
                }

                $storeId = (int)$this->_storeManager->getStore()->getId();
                $currentCustomerId = $this->getSessionCustomerId($email);
                $subscriber = $currentCustomerId
                    ? $this->subscriptionManager->subscribeCustomer($currentCustomerId, $storeId)
                    : $this->subscriptionManager->subscribe($email, $storeId);
                $message = $this->getSuccessMessage((int)$subscriber->getSubscriberStatus());
                $this->messageManager->addSuccessMessage($message);
            } catch (LocalizedException $e) {
                $this->messageManager->addComplexErrorMessage(
                    'localizedSubscriptionErrorMessage',
                    ['message' => $e->getMessage()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong with the subscription.'));
            }
        }
        /** @var Redirect $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_redirect->getRedirectUrl();
        return $redirect->setUrl($redirectUrl);
    }

    /**
     * Get customer id from session if he is owner of the email
     *
     * @param string $email
     * @return int|null
     */
    private function getSessionCustomerId(string $email): ?int
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return null;
        }

        $customer = $this->_customerSession->getCustomerDataObject();
        if ($customer->getEmail() !== $email) {
            return null;
        }

        return (int)$this->_customerSession->getId();
    }

    /**
     * Get success message
     *
     * @param int $status
     * @return Phrase
     */
    private function getSuccessMessage(int $status): Phrase
    {
        if ($status === Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }
        return __('Thank you for your subscription.');
    }
}
