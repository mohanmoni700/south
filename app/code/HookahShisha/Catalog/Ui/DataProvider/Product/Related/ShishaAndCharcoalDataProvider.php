<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Catalog
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Catalog\Ui\DataProvider\Product\Related;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider;
use Magento\Ui\DataProvider\AbstractDataProvider as SourceAbstractDataProvider;

/**
 * Class ShishaAndCharcoalDataProvider
 */
class ShishaAndCharcoalDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getLinkType()
    {
        return $this->getConfigData()['linkType'];
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getCollection()
    {
        // Allow access only to simple products
        /** @var Collection $collection */
        $collection = SourceAbstractDataProvider::getCollection()->addFilter('type_id', 'simple');;
        $collection->addAttributeToSelect('status');

        if ($this->getStore()) {
            $collection->setStore($this->getStore());
        }

        if (!$this->getProduct()) {
            return $collection;
        }

        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getProduct()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }
}
