<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Customer\Account;

use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Framework\Locale\TranslatedLists;
use Magento\Framework\Filter\Template as TemplateFilter;
use Magedelight\Subscribenow\Model\Service\PaymentService;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\CollectionFactory as SubscriberFactory;
use Magento\Theme\Block\Html\Pager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

class Profile extends Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
   protected $scopeConfig;

   protected $request;

    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    protected $imageHelper;

    protected $productFactory;

    /**
     * @var Registry
     */
    private $registry;
    
    /**
     * SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * Data
     */
    private $helper;

    /**
     * @var TranslatedLists
     */
    private $translatedLists;

    /**
     * @var TemplateFilter
     */
    private $templateFilter;

    /**
     * @var PaymentService
     */
    private $paymentService;
   
    /**
     * PaymentHelper
     */
    private $paymentHelper;
    
    /**
     * CustomerSession
     */
    private $customerSession;

    /**
     * @var TimezoneInterface
     */
    private $timezone;
    
    /**
     * @var \Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\Collection
     */
    private $subscriptionOrders;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_json;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param PaymentHelper $paymentHelper
     * @param CustomerSession $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        PageFactory $resultPageFactory,
        Image $imageHelper,
        ProductFactory $productFactory,
        Data $helper,
        TranslatedLists $translatedLists,
        TemplateFilter $templateFilter,
        PaymentService $paymentService,
        PaymentHelper $paymentHelper,
        CustomerSession $customerSession,
        Registry $registry,
        SubscriberFactory $subscriberFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        TimezoneInterface $timezone,
        \Magento\Framework\Serialize\Serializer\Json $json,
        $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->pricingHelper = $pricingHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->translatedLists = $translatedLists;
        $this->templateFilter = $templateFilter;
        $this->paymentService = $paymentService;
        $this->paymentHelper = $paymentHelper;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->subscriberFactory = $subscriberFactory;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->timezone = $timezone;
        $this->_json = $json;
        parent::__construct($context, $data);
    }

    /**
     * Render Template File
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        if ($this->getSubscription()) {
            /** @var \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(Pager::class, 'subscribenow.account.profile.pager');
            $pager->setCollection($this->getSubscription());
            $this->setChild('pager', $pager);
            if($this->request->getParams())
            {  
                $profileid = $this->request->getParam('profileid');
                    if($profileid):
                        $this->getSubscription()->addFieldToFilter('profile_id', $profileid);
                    endif;
                $status = $this->request->getParam('status');
                    if($status):
                        $this->getSubscription()->addFieldToFilter('subscription_status', $status);
                    endif;
                $nxtOrderFromDt = $this->request->getParam('fromdate');
                $nxtOrderToDt = $this->request->getParam('todate');
                    if($nxtOrderFromDt && $nxtOrderToDt):
                        $startDate = date("Y-m-d H:i:s",strtotime($nxtOrderFromDt)); // start date
                        $endDate = date("Y-m-d H:i:s", strtotime($nxtOrderToDt)); // end date
                        $this->getSubscription()->addFieldToFilter('next_occurrence_date', array('from'=>$startDate, 'to'=>$endDate));
                    endif;  
            }
            $this->getSubscription()->load();
        }
        
        return $this;
    }

    /**
     * Get Current Customer ID
     * @return int
     */
    private function getCustomerId()
    {
        return $this->customerSession->create()->getCustomerId();
    }


    /**
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomer()
    {
        return $this->customerSession->create()->getCustomer();
    }

    /**
     * Get Subscription Collection
     * @return \Magedelight\Subscribenow\Model\ResourceModel\ProductSubscribers\Collection
     */
    public function getSubscription()
    {
        if (!$this->subscriptionOrders) {
            $this->subscriptionOrders = $this->subscriberFactory->create()
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->setOrder('subscription_status', 'ASC')
            ->setOrder('next_occurrence_date','ASC');
        }
        return $this->subscriptionOrders;
    }

    /**
     * Previous page URL
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->getUrl('customer/account/');
    }

    /**
     * Return subscription status
     * @param int $statusId
     * @return string|null
     */
    public function getSubscriptionStatus($statusId)
    {
        $status = $this->helper->getStatusLabel();
        return $status[$statusId];
    }

    public function getProductImageUrl($id)
    {
        try 
        {
            $product = $this->productFactory->create()->load($id);
        } 
        catch (NoSuchEntityException $e) 
        {
            return 'Data not found';
        }
        $url = $this->imageHelper->init($product, 'product_base_image')->constrainOnly(FALSE)->keepAspectRatio(TRUE)->keepFrame(FALSE)->resize(125)->getUrl();
        return $url;
    }

    public function getProductOption($data)
    {
        $result = [];
        $productOptions =  $this->_json->unserialize($data);
        $productOptions =  isset($productOptions['product_options']) ? $productOptions['product_options'] : '';
        if ($productOptions) {
                foreach ($productOptions as $optionKey => $option) {
                    if (in_array($optionKey, ['options', 'attributes_info'])) { // bundle_options
                        foreach ($option as $opt) {
                            $result[] = $opt;
                        }
                    }
                }
         }

        return $result;
    }

    /**
     * Return Payment method code label
     * @param int $subscriptionId
     * @return string
     */
    public function getViewUrl($subscriptionId)
    {
        return $this->getUrl('subscribenow/account/summary', ['id' => $subscriptionId]);
    }

    public function getProductImage($productId)
    {
        return $this->getUrl('subscribenow/account/view', ['id' => $subscriptionId]);
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    /**
     * Return next occurrence date
     * @param type $subscription
     * @return string
     */
    public function getNextOccurrenceDate($subscription)
    {
        $status = $subscription->getSubscriptionStatus();
        $date = $subscription->getNextOccurrenceDate();
        
        if (!$date || $date == '0000-00-00 00:00:00'
            || $status == ProfileStatus::COMPLETED_STATUS
            || $status == ProfileStatus::CANCELED_STATUS
            || $status == ProfileStatus::SUSPENDED_STATUS
            || $status == ProfileStatus::FAILED_STATUS
        ) {
            return '-';
        }

        $date = strtok($subscription->getNextOccurrenceDate(), '');
        $date_format = '<span class="date">'.date('d', strtotime($date)).'</span>
                        <span class="month">'.date('F', strtotime($date)).', '.date('Y', strtotime($date)).'</span>';

        return $date_format;
    }


    public function getDueTime($subscription) {
        $date = strtok($subscription->getNextOccurrenceDate(), ' ');
        $date_now = date("Y-m-d");
        $month = date('F');
        $year = date('Y');
        $FirstDay = date("Y-m-d", strtotime('sunday last week'));  
        $LastDay = date("Y-m-d", strtotime('sunday this week'));
        if($date_now > $date) {
            $due = "(Missed Order)";
        } else if($date == $date_now) {
            $due = "(Due Today)";    
        } else if($date > $FirstDay && $date < $LastDay) {
            $due = "(This Week)";
        } else if($month == date('F', strtotime($date)) && $year == date('Y', strtotime($date))) {
            $due = "(This Month)";  
        } else { $due = '';}

        return $due;
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getBillingMaxCycle($subscription)
    {
        $maxCycle = $subscription->getPeriodMaxCycles();
        return ($maxCycle) ? $maxCycle : __('Repeats until failed or canceled.');
    }

      /**
     * @return bool
     */
    public function canResume($subscription)
    {
        return $subscription->getSubscriptionStatus() == ProfileStatus::PAUSE_STATUS;
    }

    /**
     * @return bool
     */
    public function canCancel($subscription)
    {
        return $this->helper->canCancelSubscription() &&
        $subscription->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS;
    }

    /**
     * @return bool
     */
    public function canSkip($subscription)
    {
        return $this->helper->canSkipSubscription() &&
            $subscription->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS &&
            $subscription->getSubscriptionStatus() != ProfileStatus::PENDING_STATUS &&
            $subscription->getSubscriptionStatus() != ProfileStatus::PAUSE_STATUS;
    }

    /**
     * @return bool
     */
    public function canPause($subscription)
    {
        return $this->helper->canPauseSubscription() &&
            $subscription->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS &&
            $subscription->getSubscriptionStatus() != ProfileStatus::PENDING_STATUS &&
            $subscription->getSubscriptionStatus() != ProfileStatus::PAUSE_STATUS;
    }

    /**
     * @return bool
     */
    public function canEdit($subscription)
    {
        return $subscription->getSubscriptionStatus() != ProfileStatus::CANCELED_STATUS &&
            $subscription->getSubscriptionStatus() != ProfileStatus::PAUSE_STATUS &&
            $subscription->getSubscriptionStatus() != ProfileStatus::PENDING_STATUS;
    }

    /**
     * @return bool
     */
    public function canUpdateProfile($subscription)
    {
        if ($this->isEditMode()) {
            return false;
        }
        $status = $subscription->getSubscriptionStatus();
        $nextOccurrence = $subscription->getNextOccurrenceDate();
        return $this->helper->isProfileEditable($status, $nextOccurrence);
    }

    /**
     * @since 200.5.0
     * @return bool
     */
    public function canRenew($subscription)
    {
        return $subscription->getSubscriptionStatus() == ProfileStatus::COMPLETED_STATUS;
    }

    /**
     * @return bool
     */
    public function isEditMode()
    {
        return (bool) $this->getRequest()->getParam('edit', false);
    }

    /**
     * @return string
     */
    public function getSkipUrl($subscriptionId)
    {
        return $this->getUrl('*/*/skip/id/'.$subscriptionId, ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getPauseUrl($subscriptionId)
    {
        return $this->getUrl('*/*/pause/id/'.$subscriptionId, ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getResumeUrl($subscriptionId)
    {
        return $this->getUrl('*/*/resume/id/'.$subscriptionId, ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getCancelUrl($subscriptionId)
    {
        return $this->getUrl('*/*/cancel/id/'.$subscriptionId, ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getEditUrl($subscriptionId)
    {
        return $this->getUrl('*/*/product/id/'.$subscriptionId, ['_current' => true, 'edit' => true]);
    }

    /**
     * @since 200.5.0
     * @return string
     */
    public function getRenewUrl($subscriptionId)
    {
        return $this->getUrl('*/*/renew/id/'.$subscriptionId, ['_current' => true]);
    }

    public function getBillingAmount($subscription)
    {
        return $this->pricingHelper->currency($subscription->getBillingAmount(), true, false);
    }

    /**
     * @param $type
     * @return string
     * @throws \Exception
     */
    public function getCustomerAddress($type, $subscription)
    {
        $address = $this->getCustomer()
                ->getAddressById($this->getSubscriptionAddressId($type, $subscription))
                ->getData();

        if ($address && !empty($address['country_id'])) {
            $address['country'] = $this->translatedLists->getCountryTranslation($address['country_id']);
            $street = (is_array($address['street'])) ? $address['street'][0] : $address['street'];
            $address['street1'] = $street;
            return $this->formatAddress($address);
        }
        
        return __('N/A');
    }

    private function getSubscriptionAddressId($type, $subscription)
    {
        return ($type == 'billing')?$subscription->getBillingAddressId():$subscription->getShippingAddressId();
    }

    /**
     * @param $address
     * @return string
     * @throws \Exception
     */
    private function formatAddress($address)
    {
        $template = $this->scopeConfig->getValue('customer/address_templates/html', ScopeInterface::SCOPE_STORE);
        return $this->templateFilter->setVariables($address)->filter($template);
    }

    public function getShippingMethod($data)
    {
        $additional_info =  $this->_json->unserialize($data);
        $additional_info =  isset($additional_info['shipping_title']) ? $additional_info['shipping_title'] : '';
        return $additional_info;
    }

    /**
     * @return bool
     */
    public function canDisplayCardInfo($subscription)
    {
        $code = $subscription->getPaymentMethodCode();
        $methods = $this->helper->getNACardInfoMethods();
        if (in_array($code, $methods)) {
            return false;
        }
        return true;
    }

    /**
    * @return mixed
    */
    public function getCardInfo($subscription)
    {
        $code = $subscription->getPaymentMethodCode();
        $token = $subscription->getPaymentToken();
        $customerId = $subscription->getCustomerId();
        $paymentService = $this->paymentService->getByPaymentCode($code, $token, $customerId);
        
        if ($paymentService) {
            return $paymentService->getCardInfo();
        }
        
        return null;
    }
}