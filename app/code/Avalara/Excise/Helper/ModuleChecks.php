<?php

namespace Avalara\Excise\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\TaxRuleRepositoryInterface;

class ModuleChecks extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store manager object
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    /**
     * @var Config
     */
    protected $avaTaxConfig;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * ModuleChecks constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $avaTaxConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        TaxRuleRepositoryInterface $taxRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $avaTaxConfig,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->avaTaxConfig = $avaTaxConfig;
        $this->backendUrl = $backendUrl;
        return parent::__construct($context);
    }

    /**
     * Get module check errors
     *
     * @return array
     */
    public function getModuleCheckErrors()
    {
        $errors = [];
        $errors = array_merge(
            $errors,
            $this->checkSslSupport()
        );

        return $errors;
    }

    /**
     * Check SSL support
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function checkSslSupport()
    {
        $errors = [];
        if (!function_exists('openssl_sign')) {
            $errors[] = __(
                'SSL must be enabled in PHP to use this extension.
                Typically, OpenSSL is used but it is not enabled on your server.
                This may not be a problem if you have some other form of SSL in place.
                For more information about OpenSSL, see %1.',
                '<a href="http://www.php.net/manual/en/book.openssl.php"
                target="_blank">http://www.php.net/manual/en/book.openssl.php</a>'
            );
        }

        return $errors;
    }
}
