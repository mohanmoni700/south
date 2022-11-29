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

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magedelight\Subscribenow\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Serialize\Serializer\Json;

class ProfileInfo extends Template
{
    
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Registry
     */
    private $coreRegistry;
    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var ProductRepositoryInterface
     */
    private $product;
    
    /**
     * @var Data
     */
    public $helper;
    
    /**
     * @var TimezoneInterface
     */
    public $timezone;
    
    /**
     * Constructor
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        Data $helper,
        TimezoneInterface $timezone,
        Json $serialize,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->timezone = $timezone;
        $this->context = $context;
        $this->serialize = $serialize;
        parent::__construct($context, $data);
    }

    /**
     * Get Subscription Details
     * @return object
     */
    public function getSubscription()
    {
        return $this->coreRegistry->registry('md_subscribenow_product_subscriber');
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubscriptionProduct()
    {
        if ($this->product == null) {
            $productId = $this->getSubscription()->getProductId();
            try {
                $this->product = $this->productRepository->getById($productId);
                $orderItemInfo = $this->getSubscription()->getOrderItemInfo();
                $this->helper->setBuyRequest($this->product, $orderItemInfo);
            } catch (\Exception $ex) {
                $this->product = null;
            }
        }
        return $this->product;
    }
    
    /**
     * @return array
     */
    public function getOrderItemInfo()
    {
        return $this->getSubscription()->getOrderItemInfo();
    }
    
    /**
     * @return bool
     */
    public function isEditMode()
    {
        $editParam = $this->context->getRequest()->getParam('edit');
        return (bool) ($editParam === 'editable');
    }
    
    /**
     * Form Submit URL
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('subscribenow/productsubscribers/save');
    }
}
