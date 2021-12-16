<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_CatalogImportExport
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\CatalogImportExport\Model\Export;

use Magento\CatalogImportExport\Model\Export\Product as SourceProduct;

class Product extends SourceProduct
{
    /**
     * Get header columns
     *
     * @return string[]
     */
    public function _getHeaderColumns(): array
    {
        return $this->_customHeadersMapping(
            $this->rowCustomizer->addHeaderColumns(array_merge($this->_headerColumns, ['shisha_skus', 'charcoal_skus']))
        );
    }
}
