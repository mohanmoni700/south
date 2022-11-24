<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Payment\Model\Config\Source\Allmethods;

class PaymentColumn extends Select
{
    /**
     * @var PaymentColumn
     */
    protected $allPaymentMethod;

    /**
     * New construct
     *
     * @param Context $context
     * @param Allmethods $allPaymentMethod
     * @param array $data
     */
    public function __construct(
        Context $context,
        Allmethods $allPaymentMethod,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->allPaymentMethod = $allPaymentMethod;
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Render option
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        return $this->allPaymentMethod->toOptionArray();
    }
}
