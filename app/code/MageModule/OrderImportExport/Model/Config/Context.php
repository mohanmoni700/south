<?php

namespace MageModule\OrderImportExport\Model\Config;

use MageModule\OrderImportExport\Model\ResourceModel\Config as ConfigResource;

/**
 * Class Context
 *
 * @package MageModule\OrderImportExport\Model\Config
 */
class Context extends \Magento\Framework\Model\Context
{
    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var string
     */
    private $configType;

    /**
     * Context constructor.
     *
     * @param ConfigResource                                        $configResource
     * @param string                                                $configType
     * @param \Magento\Framework\Event\ManagerInterface             $eventDispatcher
     * @param \Magento\Framework\App\CacheInterface                 $cacheManager
     * @param \Magento\Framework\App\State                          $appState
     * @param \Magento\Framework\Model\ActionValidator\RemoveAction $actionValidator
     * @param \Psr\Log\LoggerInterface                              $logger
     */
    public function __construct(
        ConfigResource $configResource,
        $configType,
        \Magento\Framework\Event\ManagerInterface $eventDispatcher,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Model\ActionValidator\RemoveAction $actionValidator,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $logger,
            $eventDispatcher,
            $cacheManager,
            $appState,
            $actionValidator
        );

        $this->configResource = $configResource;
        $this->configType     = $configType;
    }

    /**
     * @return ConfigResource
     */
    public function getConfigResource()
    {
        return $this->configResource;
    }

    /**
     * @return string
     */
    public function getConfigType()
    {
        return $this->configType;
    }
}
