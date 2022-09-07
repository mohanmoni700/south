<?php

namespace Alfakher\GrossMargin\Ui\Magento\Catalog\DataProvider\Product\Form;

/**
 * @author af_bv_op
 */
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        \Magento\Framework\App\Request\Http $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $collectionFactory, $pool, $meta, $data);

        $this->request = $request;

        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        /* -- af_bv_op; Start -- */
        if (isset($this->data[$this->request->getParam('id')]['product'])) {
            $price = 0;
            $cost = 0;
            $proData = $this->data[$this->request->getParam('id')]['product'];

            if (isset($proData['price'])) {
                $price = $proData['price'];
            }

            if (isset($proData['cost'])) {
                $cost = $proData['cost'];
            }

            if (!isset($proData['gross_margin']) ||
                $proData['gross_margin'] === '0' ||
                $proData['gross_margin'] === '0.00%'
            ) {
                try {
                    $grossMargin = ($price - $cost) / $price * 100;
                    $this->data[$this->request->getParam('id')]
                    ['product']['gross_margin'] = number_format($grossMargin, 2) . "%";
                } catch (\Exception $e) {
                    $this->data[$this->request->getParam('id')]['product']['gross_margin'] = "0.00%";
                }
            }
        }
        /* -- af_bv_op; End -- */

        return $this->data;
    }
}
