<?php
/**
 *  Magedelight
 *  Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Controller\Adminhtml\Productsubscribers;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Registry;
use Magedelight\Subscribenow\Model\ProductSubscribersFactory;

abstract class AbstractGridAction extends Action
{
    /**
     * @var RawFactory
     */
    public $resultRawFactory;
    
    /**
     * @var LayoutFactory
     */
    public $layoutFactory;
    
    /**
     * @var Registry
     */
    private $coreRegistry;
    
    /**
     * @var ProductSubscribersFactory
     */
    private $subscriberFactory;

    /**
     * Constructor
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param Registry $coreRegistry
     * @param ProductSubscribersFactory $productSubscriber
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        Registry $coreRegistry,
        ProductSubscribersFactory $productSubscriber
    ) {
        parent::__construct($context);
        
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->coreRegistry = $coreRegistry;
        $this->subscriberFactory = $productSubscriber;
    }

    /**
     * Set Registry Data
     */
    public function init()
    {
        $id = $this->getRequest()->getParam('id', 0);
        
        if ($id) {
            $model = $this->subscriberFactory->create()->load($id);
            $this->coreRegistry->register('md_subscribenow_product_subscriber', $model);
        }
        return;
    }
}
