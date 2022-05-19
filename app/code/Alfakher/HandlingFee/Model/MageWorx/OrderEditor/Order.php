<?php

namespace Alfakher\HandlingFee\Model\MageWorx\OrderEditor;

class Order extends \MageWorx\OrderEditor\Model\Order
{

    /**
     * Edit items
     *
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function editItems(array $params)
    {
        $this->resetChanges();
        $this->prepareParamsForEditItems($params);
        $this->updateOrderItems();
        $this->resetItems();
        $this->collectOrderTotals();
        $this->updatePayment();
        $this->resetItems();

        /* bv_op; debug; Start */
        $this->setOriginalSubtotal(0);
        $this->setOriginalSubtotalInclTax(0);
        $this->setOriginalBaseSubtotal(0);
        $this->setOriginalBaseSubtotalInclTax(0);
        $this->setTotalSubtotalDiscount(0);

        if ($this->getTotalShippingFeeDiscount() > 0) {
            $this->setShippingAmount($this->getOriginalShippingFee());
            $this->setBaseShippingAmount($this->getOriginalBaseShippingAmount());
            $this->setShippingInclTax($this->getOriginalShippingInclTax());
            $this->setBaseShippingInclTax($this->getOriginalBaseShippingInclTax());
            $this->setBaseGrandTotal($this->getBaseGrandTotal() + $this->getTotalShippingFeeDiscount());
            $this->setGrandTotal($this->getGrandTotal() + $this->getTotalShippingFeeDiscount());
        } elseif ($this->getOriginalShippingFee() > 0) {
            $this->setShippingAmount($this->getOriginalShippingFee());
            $this->setBaseShippingAmount($this->getOriginalBaseShippingAmount());
            $this->setShippingInclTax($this->getOriginalShippingInclTax());
            $this->setBaseShippingInclTax($this->getOriginalBaseShippingInclTax());
            $this->setBaseGrandTotal($this->getBaseGrandTotal() + $this->getOriginalShippingFee());
            $this->setGrandTotal($this->getGrandTotal() + $this->getOriginalShippingFee());
        }

        $this->setOriginalShippingFee(0);
        $this->setOriginalBaseShippingAmount(0);
        $this->setOriginalShippingInclTax(0);
        $this->setOriginalBaseShippingInclTax(0);
        $this->setTotalShippingFeeDiscount(0);
        $this->setHandlingFee(0);
        /* bv_op; debug; End */

        $this->orderRepository->save($this);

        $this->_eventManager->dispatch(
            'mageworx_save_logged_changes_for_order',
            [
                'order_id' => $this->getId(),
                'notify_customer' => false,
            ]
        );
    }
}
