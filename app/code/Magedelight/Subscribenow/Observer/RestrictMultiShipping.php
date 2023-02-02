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

namespace Magedelight\Subscribenow\Observer;

use Magedelight\Subscribenow\Helper\Data;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class RestrictMultiShipping
 *
 * Restrict MultiShipping Functionality if cart
 * have any subscription item
 *
 * @since 200.5.1
 * @package Magedelight\Subscribenow\Observer
 */
class RestrictMultiShipping implements ObserverInterface
{
    private $helper;
    private $url;
    private $messageManager;

    public function __construct(
        Data $helper,
        UrlInterface $url,
        ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->url = $url;
        $this->messageManager = $messageManager;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->helper->hasSubscriptionProduct()) {
            $controller = $observer->getControllerAction();
            $this->messageManager->addErrorMessage(__('Subscription item does not support multi-shipping, Remove subscription item if you want to checkout with multi shipping.'));
            $url = $this->url->getUrl('checkout/cart'); //Magento\Framework\UrlInterface $url
            $controller->getResponse()->setRedirect($url);
        }

        return $this;
    }
}
