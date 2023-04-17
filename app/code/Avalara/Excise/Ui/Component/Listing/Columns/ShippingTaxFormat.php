<?php
declare(strict_types=1);

namespace Avalara\Excise\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;

class ShippingTaxFormat extends Column
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $currencySymbol = $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['excise_tax'])):
                    $item['excise_tax'] =
                    $currencySymbol.number_format((float)$item['excise_tax'], 2, '.', '');
                endif;

                if (isset($item['sales_tax'])):
                    $item['sales_tax'] =
                    $currencySymbol.number_format((float)$item['sales_tax'], 2, '.', '');
                endif;

                if (isset($item['base_shipping_tax_amount'])):
                      $item['base_shipping_tax_amount'] =
                      $currencySymbol.number_format((float)$item['base_shipping_tax_amount'], 2, '.', '');
                endif;
            }
        }
        return $dataSource;
    }
}
