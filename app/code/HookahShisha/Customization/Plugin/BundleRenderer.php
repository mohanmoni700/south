<?php

namespace HookahShisha\Customization\Plugin;

use Magento\Backend\Block\Template;

/**
 * Set template for bundle items
 */
class BundleRenderer
{
    /**
     * BeforeToHtml
     *
     * @param template $originalBlock
     */

    public function beforeToHtml(Template $originalBlock)
    {
        $originalBlock->setTemplate('HookahShisha_Customization::order/view/bundle.phtml');
    }
}
