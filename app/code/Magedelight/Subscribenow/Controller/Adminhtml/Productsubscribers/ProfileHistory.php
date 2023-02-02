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

use Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\Grid\ProfileHistory as ProfileHistoryGrid;

class ProfileHistory extends AbstractGridAction
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->init();
        
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                ProfileHistoryGrid::class,
                'subscribenow.profilehistory.grid'
            )->toHtml()
        );
    }
}
