<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace HookahShisha\Import\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Import block
 */
class Import extends Template
{
    /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Template\Context $context
     * @param array $data = []
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Template\Context $context,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve getMediaPath
     *
     * @return string
     * @since 100.1.0
     */
    public function getMediaPath()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }
}
