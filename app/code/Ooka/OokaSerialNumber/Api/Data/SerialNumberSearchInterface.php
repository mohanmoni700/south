<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface SerialNumberSearchInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return SerialNumberInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param SerialNumberInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
