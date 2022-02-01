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
 * @author        MageModule admin@magemodule.com
 * @copyright    2019 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\OrderImportExport\Plugin\Framework\Event;

use MageModule\OrderImportExport\Model\Registry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\InvokerInterface;

/**
 * Class Invoker
 *
 * @package MageModule\OrderImportExport\Plugin\Framework\Event
 */
class Invoker
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $bannedObservers;

    /**
     * Invoker constructor.
     *
     * @param Registry $registry
     * @param array    $bannedObservers
     */
    public function __construct(
        Registry $registry,
        $bannedObservers = []
    ) {
        $this->registry        = $registry;
        $this->bannedObservers = $bannedObservers;
    }

    /**
     * @param InvokerInterface $subject
     * @param array            $configuration
     * @param Observer         $observer
     *
     * @return array
     */
    public function beforeDispatch($subject, array $configuration, Observer $observer)
    {
        if ($this->registry->isOrderImportExport()) {
            if (isset($this->bannedObservers[$configuration['instance']]) &&
                $this->bannedObservers[$configuration['instance']] === $configuration['name']
            ) {
                $configuration['disabled'] = true;
            }
        }

        return [$configuration, $observer];
    }
}
