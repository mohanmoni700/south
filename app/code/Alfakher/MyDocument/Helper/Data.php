<?php
namespace Alfakher\MyDocument\Helper;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        CustomerFactory $customer,
        array $data = []
    ) {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->customer = $customer;
        $this->storeManager = $storeManager;
    }

    public function sendMail($post, $customerid)
    {
        foreach ($post as $value) {
            $x[] = $value['status'];
        }
        if (in_array(0, $x)) {
            $msg = "rejected";
        } else {
            $msg = "accept";
        }

        $customer = $this->customer->create()->load($customerid);
        $customerEmail = $customer->getEmail();
        $this->_inlineTranslation->suspend();
        $fromEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
        $fromName = $this->_scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);

        $sender = [
            'name' => $fromName,
            'email' => $fromEmail,
        ];

        $transport = $this->_transportBuilder
            ->setTemplateIdentifier('custom_email')
            ->setTemplateOptions(
                [
                    'area' => 'adminhtml',
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars([
                'msg' => $msg,
            ])
            ->setFromByScope($sender)
            ->addTo([$customerEmail])
            ->getTransport();

        $transport->sendMessage();
    }
}
