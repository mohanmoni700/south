<?php
declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Ui\Componets\Serialnumber\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class OrderId extends Column
{
    /**
     * @var OrderInterfaceFactory
     */
    protected $orderFactory;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param OrderInterfaceFactory $orderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OrderInterfaceFactory $orderFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare datasource order id url
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $order = $this->orderFactory->create()->loadByIncrementId($item['order_id']);
                if (isset($item['order_id'])) {
                    $url = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getEntityId()]);
                    $link = '<a href="' . $url . '"">' . $item['order_id'] . '</a>';
                    $item['order_id'] = $link;
                }
            }
        }
        return $dataSource;
    }
}
