<?php
namespace Infibeam\Ccavenue\Block\Transparent;

use Magento\Framework\View\Element\Template;

class Redirect extends Template
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;
    /**
     * @var \Infibeam\Ccavenue\Logger\Logger
     */
    protected $_logger;
    /**
     * Redirect constructor.
     * @param Template\Context $context
     * @param \Magento\Framework\UrlInterface $url
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\UrlInterface $url,
        \Infibeam\Ccavenue\Logger\Logger $logger,
        array $data = []
    ) {
        $this->url = $url;
        $this->_logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * Returns url for redirect.
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->url->getUrl("ccavenue/standard/response");
    }

    /**
     * Returns params to be redirected.
     * @return array
     */
    public function getPostParams()
    {
        $postParams = (array)$this->_request->getPostValue();
        return $postParams;
    }
}