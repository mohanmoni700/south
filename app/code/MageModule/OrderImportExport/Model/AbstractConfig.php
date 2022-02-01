<?php
/**
 * Copyright (c) 2019 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2019 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\OrderImportExport\Model;

/**
 * Class AbstractConfig
 *
 * @package MageModule\OrderImportExport\Model
 */
abstract class AbstractConfig extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ResourceModel\Config
     */
    private $configResource;

    /**
     * @var string
     */
    private $configType;

    /**
     * AbstractConfig constructor.
     *
     * @param Config\Context                                               $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $defaultOptions
     * @param array                                                        $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \MageModule\OrderImportExport\Model\Config\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $defaultOptions = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->configResource = $context->getConfigResource();
        $this->configType     = $context->getConfigType();

        if (is_array($defaultOptions)) {
            $this->addData($defaultOptions);
        }

        $value = $this->configResource->getConfig($this->configType);
        if (is_array($value) && $value) {
            $this->addData($value);
        }
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveConfig()
    {
        $this->configResource->saveConfig($this->configType, $this->getData());

        return $this;
    }
}
