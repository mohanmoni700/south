<?php
namespace HookahShisha\Customerb2b\Helper;

use HookahShisha\Customerb2b\Model\Company\Source\AnnualTurnOver;
use HookahShisha\Customerb2b\Model\Company\Source\Businesstype;
use HookahShisha\Customerb2b\Model\Company\Source\HearAboutUs;
use HookahShisha\Customerb2b\Model\Company\Source\NumberOfEmp;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Url;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Helper to sent b2b form infom email to the admin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var AnnualTurnOver
     */
    protected $annualturnover;

    /**
     * @var Businesstype
     */
    protected $businesstype;

    /**
     * @var HearAboutUs
     */
    protected $hearaboutus;

    /**
     * @var NumberOfEmp
     */
    protected $numberffemp;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     * @param AnnualTurnOver $annualturnover
     * @param Businesstype $businesstype
     * @param HearAboutUs $hearaboutus
     * @param NumberOfEmp $numberffemp
     * @param RegionFactory $regionFactory
     * @param Url $urlHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        AnnualTurnOver $annualturnover,
        Businesstype $businesstype,
        HearAboutUs $hearaboutus,
        NumberOfEmp $numberffemp,
        RegionFactory $regionFactory,
        Url $urlHelper
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->annualturnover = $annualturnover;
        $this->businesstype = $businesstype;
        $this->hearaboutus = $hearaboutus;
        $this->numberffemp = $numberffemp;
        $this->regionFactory = $regionFactory;
        $this->urlHelper = $urlHelper;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function sendEmail($data)
    {
        if (isset($data['region_id'])) {
            $region_id = $data['region_id'];
            $region = $this->regionFactory->create()->load($region_id);
            $region_name = $region->getName();
        } else {
            $region_name = $data['region'];
        }

        $annual_turnover = $this->annualturnover->getOptionArray();
        $annual_turn_over = $annual_turnover[$data['company']['annual_turn_over']];

        $businesstypes = $this->businesstype->getOptionArray();
        $business_type = $businesstypes[$data['company']['business_type']];

        $numberof_emp = $this->numberffemp->getOptionArray();
        $number_of_emp = $numberof_emp[$data['company']['number_of_emp']];

        $hearabout_us = $this->hearaboutus->getOptionArray();
        $hear_about_us = $hearabout_us[$data['company']['hear_about_us']];

        try {
            $templateId = 'b2b_customer_admin_email_template';

            $street = $data['street'];
            $streetm = implode(',', $street);

            $fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $fromName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);

            $templateVars = [
                'email' => $data['email'],
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'telephone' => $data['telephone'],
                'country_id' => $data['country_id'],
                'region' => $region_name,
                'country_id' => $data['country_id'],
                'city' => $data['city'],
                'postcode' => $data['postcode'],
                'street' => $streetm,
                'company_name' => $data['company']['company_name'],
                'business_type' => $business_type,
                'annual_turn_over' => $annual_turn_over,
                'number_of_emp' => $number_of_emp,
                'vat_tax_id' => $data['company']['vat_tax_id'],
                'tin_number' => $data['company']['tin_number'],
                'tobacco_permit_number' => $data['company']['tobacco_permit_number'],
                'hear_about_us' => $hear_about_us,
                'questions' => $data['company']['questions'],

            ];

            $storeId = $this->storeManager->getStore()->getId();

            $receiver = $this->scopeConfig->getValue(
                'hookahshisha/b2bsendmail/b2bcstadminsendmail',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $toEmail = explode(',', $receiver);

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getReorderUrl($itemId)
    {
        return $this->urlHelper->getUrl(
            'customerb2b/order/reorderproduct',
            [
                'order_item' => $itemId,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function sendRejectEmail($isCstCom, $templateVars, $status)
    {
        try {
            $this->inlineTranslation->suspend();
            $fromEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            $fromName = $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
            $sender = [
                'name' => $fromName,
                'email' => $fromEmail,
            ];
            $toEmail = [$templateVars['email']];
            if ($isCstCom == "reject") {
                $emailIdentifier = "b2b_company_customer_account_reject_email";
            } else {
                $emailIdentifier = "b2b_company_customer_account_verfiy_email";
            }
            $storeId = $templateVars['store_id'];
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailIdentifier, $storeScope)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getEmployees($emp)
    {
        $numberof_emp = $this->numberffemp->getOptionArray();
        if (in_array($emp, $numberof_emp)) {
            return $numberof_emp[$emp];
        }
    }
}
