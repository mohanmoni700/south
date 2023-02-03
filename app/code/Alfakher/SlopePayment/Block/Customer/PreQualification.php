<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Cms\Block\Block as CmsBlock;
use Magento\Store\Model\ScopeInterface;

class PreQualification extends Template
{
    public const XML_PATH_PREQUALIFY_CONTENT = 'payment/slope_payment/prequalifycontent';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Template\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve CMS block for slope prequalification content
     *
     * @return string
     */
    public function getSlopePreQualificationContent()
    {
        $blockIdentifier = $this->scopeConfig->getValue(
            self::XML_PATH_PREQUALIFY_CONTENT,
            ScopeInterface::SCOPE_STORE
        );
        $block = $this->getLayout()->createBlock(CmsBlock::class)->setBlockId($blockIdentifier);

        if ($block) {
            return $block->toHtml();
        }
    }
}
