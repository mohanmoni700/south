<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Adminhtml\Config;

class Interval extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var \Magedelight\Subscribenow\Block\Adminhtml\Config\IntervalType
     */
    protected $IntervalTypeRenderer = null;

    /**
     * @var string
     */
    protected $_template = 'Magedelight_Subscribenow::system/config/form/field/array.phtml';

    /**
     * Returns renderer for intervaltype element.
     *
     * @return \Magedelight\Subscribenow\Block\Adminhtml\Config\IntervalType
     */
    protected function getIntervalTypeRenderer()
    {
        if (!$this->IntervalTypeRenderer) {
            $this->IntervalTypeRenderer = $this->getLayout()->createBlock(
                '\Magedelight\Subscribenow\Block\Adminhtml\Config\IntervalType',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->IntervalTypeRenderer;
    }

    /**
     * Prepare to render.
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'interval_type',
            [
            'label' => __('Interval Type'),
            'renderer' => $this->getIntervalTypeRenderer(),
                ]
        );

        $this->addColumn(
            'no_of_interval',
            [
            'label' => __('Number Of Interval'),
            'class' => 'validate-number validate-digits required validate-greater-than-zero',
                ]
        );

        $this->addColumn(
            'interval_label',
            [
            'label' => __('Interval Label'),
            'style' => 'width:96px',
            'class' => 'required',
                ]
        );

        $this->renderCellTemplate('no_of_interval');

        $this->renderCellTemplate('interval_label');

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $intervalTypes = $row->getIntervalType();
        if ($intervalTypes) {
            if (!is_array($intervalTypes)) {
                $intervalTypes = [$intervalTypes];
            }
            foreach ($intervalTypes as $intervalType) {
                $options['option_' . $this->getIntervalTypeRenderer()->calcOptionHash($intervalType)] = 'selected="selected"';
            }
        }
        $row->setData('option_extra_attrs', $options);

        return;
    }
}
