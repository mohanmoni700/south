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

namespace Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab;

use Magento\Backend\Block\Template;
use Magedelight\Subscribenow\Block\Adminhtml\ProductSubscribers\View\Tab\Grid\ProfileHistory as ProfileHistoryGrid;

class ProfileHistory extends Template
{

    
    /**
     * @var ProfileHistoryGrid
     */
    private $blockGrid;

    /**
     * Retrieve instance of grid block
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                ProfileHistoryGrid::class,
                'subscribenow.profilehistory.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }
}
