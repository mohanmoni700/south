<?php

namespace Alfakher\Productpageb2b\Block\Product;

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

class Breadcrumbs extends \Magento\Theme\Block\Html\Breadcrumbs
{

    /**
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * @var Path
     */
    protected $path = [];

    /**
     * @param Context $context
     * @param Data $catalogData
     * @param array $data
     */
    public function __construct(Context $context, Data $catalogData, array $data = [])
    {
        $this->_catalogData = $catalogData;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getTitleSeparator($store = null)
    {
        $separator = (string) $this->_scopeConfig->getValue('catalog/seo/title_separator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        return ' ' . $separator . ' ';
    }

    /**
     * @inheritDoc
     */
    public function getBreadcrumb()
    {
        $this->addCrumb(
            'home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->getBaseUrl(),
            ]
        );
        foreach ((array) $this->path as $name => $breadcrumb) {
            $this->addCrumb($name, $breadcrumb);
        }
        return $this->getCrumbs();
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        $this->path = $this->_catalogData->getBreadcrumbPath();
        $title = [];
        foreach ((array) $this->path as $name => $breadcrumb) {
            $title[] = $breadcrumb['label'];
        }
        return $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($title)));
        //return parent::_prepareLayout();
    }

    /**
     * @inheritDoc
     */
    public function getCrumbs()
    {
        return $this->_crumbs;
    }

    /**
     * @inheritDoc
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
