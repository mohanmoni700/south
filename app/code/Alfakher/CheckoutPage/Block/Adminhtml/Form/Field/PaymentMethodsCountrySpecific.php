<?php
declare(strict_types=1);

namespace Alfakher\CheckoutPage\Block\Adminhtml\Form\Field;

use Alfakher\CheckoutPage\Block\Adminhtml\Form\Field\PaymentColumn;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class PaymentMethodsCountrySpecific extends AbstractFieldArray
{
    /**
     * @var PaymentColumn
     */
    private $paymentMethodRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('payment_method', [
            'label' => __('Payment Method'),
            'renderer' => $this->getPaymentMethodRenderer(),
        ]);
        $this->addColumn('country_codes', [
            'label' => __('Country Codes'),
            'class' => 'required-entry',
            'comment' => 'comma seperated'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @inheritDoc
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $paymentMethod = $row->getPaymentMethod();
        if ($paymentMethod !== null) {
            $options[
                'option_' . $this->getPaymentMethodRenderer()->calcOptionHash($paymentMethod)
            ] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @inheritDoc
     */
    private function getPaymentMethodRenderer()
    {
        if (!$this->paymentMethodRenderer) {
            $this->paymentMethodRenderer = $this->getLayout()->createBlock(
                PaymentColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->paymentMethodRenderer;
    }
}
