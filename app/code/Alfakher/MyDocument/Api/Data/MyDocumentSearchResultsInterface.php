<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Alfakher\MyDocument\Api\Data;

interface MyDocumentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get MyDocument list.

     * @return \Alfakher\MyDocument\Api\Data\MyDocumentInterface[]
     */
    public function getItems();

    /**
     * Set customer_id list.

     * @param \Alfakher\MyDocument\Api\Data\MyDocumentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
