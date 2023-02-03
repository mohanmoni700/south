<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Cms\Block\Block as CmsBlock;
use Magento\Store\Model\ScopeInterface;

class PreQualification extends Template
{
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
            'payment/slope_payment/prequalifycontent',
            ScopeInterface::SCOPE_STORE
        );
        $block = $this->getLayout()->createBlock(CmsBlock::class)->setBlockId($blockIdentifier);

        if ($block) {
            return $block->toHtml();
        }
    }
}
