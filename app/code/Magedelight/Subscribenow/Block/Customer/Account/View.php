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

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magedelight\Subscribenow\Helper\Data as SubscribeHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Serialize\Serializer\Json;

class View extends AbstractSubscription
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $product;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serialize;
    
    /**
     * Constructor
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        SubscribeHelper $subscribeHelper,
        TimezoneInterface $timezone,
        ProductRepositoryInterface $productRepository,
        Json $serialize,
        array $data = []
    ) {
        parent::__construct($context, $registry, $subscribeHelper, $timezone, $data);
        $this->productRepository = $productRepository;
        $this->serialize = $serialize;
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Subscription # %1', $this->getSubscription()->getProfileId()));
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
                $this->subscribeHelper->setBuyRequest($this->product, $orderItemInfo);
            } catch (\Exception $ex) {
                $this->product = null;
            }
        }
        return $this->product;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/profile/');
    }

    /**
     * @return string
     */
    public function getProductEditUrl()
    {
        return $this->getUrl('*/*/productedit/', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getProfileEditUrl()
    {
        return $this->getUrl('*/*/profileedit/', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getAddressEditUrl()
    {
        return $this->getUrl('*/*/addressedit/', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getPaymentEditUrl()
    {
        return $this->getUrl('*/*/paymentedit/', ['_current' => true]);
    }

    /**
     * @return bool
     */
    public function isEditMode()
    {
        return (bool) $this->getRequest()->getParam('edit', false);
    }
}
