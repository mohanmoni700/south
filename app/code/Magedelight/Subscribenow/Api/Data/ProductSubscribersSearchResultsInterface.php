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

namespace Magedelight\Subscribenow\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface ProductSubscribersSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Subscription list.
     *
     * @return \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface[]
     */
    public function getItems();

    /**
     * Set Subscription list.
     *
     * @param \Magedelight\Subscribenow\Api\Data\ProductSubscribersInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
