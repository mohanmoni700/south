<?php
/**
 * @author Renato Medina <medina@mdnsolutions.com>
 * @package MDN_Newsletter
 */
namespace Alfakher\AddtocartPriceHide\Controller\Plugin\Subscriber;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        SubscriptionManagerInterface $subscriptionManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
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
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string) $this->getRequest()->getPost('email');
            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);
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
                    'msg' => __('There was a problem with the subscription: %1', $e->getMessage()),
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
