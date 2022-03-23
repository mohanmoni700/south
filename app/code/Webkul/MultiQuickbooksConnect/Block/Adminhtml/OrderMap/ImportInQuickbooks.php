<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\OrderMap;

class ImportInQuickbooks extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Model\OrderMapFactory
     */
    private $orderMapFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\MultiQuickbooksConnect\Model\OrderMapFactory $orderMapFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MultiQuickbooksConnect\Model\OrderMapFactory $orderMapFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
        $this->jsonHelper = $jsonHelper;
        $this->orderMapFactory = $orderMapFactory;
    }

    /**
     * For get selected order data.
     * @return array
     */
    public function getOrderList()
    {
        $paramsData = $this->getRequest()->getParams();
        $accountId = $paramsData['account_id'];
        $orderEntityIds = $paramsData['orderEntityIds'];
        $itemsList =  $this->orderFactory->create()->getCollection()
                                ->addFieldToFilter('entity_id', ['in' => $orderEntityIds])
                                ->addAttributeToSelect('entity_id')->toArray();
        return [
            'items_json' => $this->jsonHelper->jsonEncode($itemsList['items']),
            'items_count' => count($itemsList['items']),
            'account_id' => $accountId
        ];
    }
}
