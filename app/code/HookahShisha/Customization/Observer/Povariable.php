<?php

namespace HookahShisha\Customization\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\ScopeInterface;

class Povariable implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    protected $scopeConfig;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Execute
     *
     * @param mixed $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getSource();
        if (!$order instanceof \Magento\Sales\Model\Order) {
            $order = $order->getOrder();
        }
        $Purchase = $order->getPurchaseOrder();
        $observer->getVariableList()->setData('purchase_order', $Purchase);

        $storeId = $order->getStoreId();

        if ($order->getExciseTax() > 0) {
            $incl = $this->getConfigdata('hookahshisha/excise_tax_note/incl_excise_tax_note', $storeId);
            $observer->getVariableList()->setData('excise_tax', $incl);
        } else {
            $excl = $this->getConfigdata('hookahshisha/excise_tax_note/excl_excise_tax_note', $storeId);
            $observer->getVariableList()->setData('excise_tax', $excl);
        }
    }

    /**
     * Configuration field
     *
     * @param mixed $fieldName
     * @param int $storeId
     */
    public function getConfigdata($fieldName, $storeId)
    {
        return $this->scopeConfig->getValue($fieldName, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
