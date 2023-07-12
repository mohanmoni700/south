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

namespace Magedelight\Subscribenow\Block\Adminhtml\Order\View\Tab;

class SubscriptionProfiles extends \Magento\Framework\View\Element\Text\ListText implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magedelight\Subscribenow\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Subscribenow\Model\ResourceModel\Order\ProductSubscribers\Grid\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Subscribenow\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magedelight\Subscribenow\Helper\Data $helper,
        \Magedelight\Subscribenow\Model\ResourceModel\Order\ProductSubscribers\Grid\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Subscription Profiles');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Subscription Profiles');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if ($this->helper->isModuleEnable()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        $collection = $this->collectionFactory->create()->addOrderFilter($this->getOrder()->getId());
        if ($collection->getSize() == 0) {
            return true;
        }
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
