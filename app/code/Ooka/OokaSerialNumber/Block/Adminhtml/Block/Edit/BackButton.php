<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Block\Adminhtml\Block\Edit;

use Magento\Cms\Block\Adminhtml\Block\Edit\BackButton as MagentoBackButton;

class BackButton extends MagentoBackButton
{
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('serialnumber/serialnumber/index');
    }
}
