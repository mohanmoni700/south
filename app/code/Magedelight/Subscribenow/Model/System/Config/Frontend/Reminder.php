<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\System\Config\Frontend;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Reminder extends Field
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();

        $html .= "<script type='text/javascript'>
        require([
            'jquery', 
            'jquery/ui', 
            'jquery/validate', 
            'mage/translate'
            ], function($){ 
                $.validator.addMethod('reminder-greater', function (value) {
                    var MdUpdateBefore = $('#md_subscribenow_general_update_profile_before').val();
                    var MdReminder = parseInt(value);
                    
                    if(MdReminder && MdReminder < MdUpdateBefore) {
                        return false;
                    }
                    return true;
                },
                $.mage.__('Reminder days must be greater then allow to update profile before'));
                
            }
        ); </script>";

        return $html;
    }
}
