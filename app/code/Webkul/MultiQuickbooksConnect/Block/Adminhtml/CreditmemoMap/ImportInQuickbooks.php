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
namespace Webkul\MultiQuickbooksConnect\Block\Adminhtml\CreditmemoMap;

class ImportInQuickbooks extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection
     */
    private $cmemoCollectionFactory;

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
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $cmemoCollectionFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\MultiQuickbooksConnect\Model\OrderMapFactory $orderMapFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $cmemoCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MultiQuickbooksConnect\Model\OrderMapFactory $orderMapFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cmemoCollectionFactory = $cmemoCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->orderMapFactory = $orderMapFactory;
    }

    /**
     * For get selected creditmemo data
     * @return array
     */
    public function getCreditmemoList()
    {
        $paramsData = $this->getRequest()->getParams();
        $accountId = $paramsData['account_id'];
        $orderEntityIds = $paramsData['creditmemoEntityIds'];
        $itemsList =  $this->cmemoCollectionFactory->create()
                        ->addFieldToFilter('entity_id', ['in' => $orderEntityIds])
                        ->addAttributeToSelect('entity_id')->toArray();
        return [
            'items_json' => $this->jsonHelper->jsonEncode($itemsList['items']),
            'items_count' => count($itemsList['items']),
            'account_id' => $accountId
        ];
    }
}
