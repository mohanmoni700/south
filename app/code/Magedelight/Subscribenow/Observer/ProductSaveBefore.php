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

namespace Magedelight\Subscribenow\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveBefore implements ObserverInterface
{

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_messageManager = $messageManager;
        $this->_responseFactory = $responseFactory;
        $this->redirect = $redirect;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $enableSubscription = $product->getIsSubscription();
        $enableTrialPerioad = $product->getAllowTrial();
        
        if ($enableSubscription) {
            $redirectUrl = $this->redirect->getRedirectUrl();
            $initialFee = $product->getInitialAmount();
            $trialFee = $product->getTrialAmount();

            if ($enableTrialPerioad) {
                if ($trialFee <= 0 && $initialFee <= 0) {
                    $message = __('Subscribe Now Trial Fee must be greater than 0');
                    $this->_messageManager->addErrorMessage($message);
                    $this->_responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
                }
            } else {
                $disocuntType = $product->getDiscountType();
                $disocunt = $product->getDiscountAmount();
                if ($disocuntType == 'percentage' && $disocunt > 100) {
                    $product->setDiscountAmount(100);
                }
            }
        }
    }
}
