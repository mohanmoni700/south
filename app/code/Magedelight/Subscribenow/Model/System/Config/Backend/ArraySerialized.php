<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\System\Config\Backend;

class ArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized
{

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWritter,
        \Magento\Framework\Message\ManagerInterface $messagemanager,
        array $data = []
    ) {
        $this->_eventmanager = $context->getEventDispatcher();
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->configWritter = $configWritter;
        $this->messageManager = $messagemanager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Unset array element with '__empty' key
     * Magedelight set subscription option to no if no interval found(As per discussion 652016).
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->_eventmanager->dispatch('md_subscribenow_system_config_changed');
        $value = $this->getValue();
        $isenabled = $this->_config->getValue('md_subscribenow/general/enabled', $this->getScope());

        if (is_array($value)) {
            unset($value['__empty']);
        }

        if (count($value) == 0) {
            $this->disableSubscribenow();
        }

        $this->setValue($value);
        $types = ['config', 'full_page'];

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }

        return parent::beforeSave();
    }

    public function disableSubscribenow()
    {
        $this->configWritter->save('md_subscribenow/general/enabled', 0, $this->getScope(), $this->getScopeId());
        $this->messageManager->addNotice('Make sure to add Subscription Interval type before enabling subscribe now.');
    }
}
