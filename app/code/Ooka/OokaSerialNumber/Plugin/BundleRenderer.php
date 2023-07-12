<?php
declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Plugin;

use Magento\Backend\Block\Template;
use Ooka\OokaSerialNumber\ViewModel\SerialCode;

class BundleRenderer
{
    /**
     * @var SerialCode
     */
    private SerialCode $serialCode;

    /**
     * @param SerialCode $serialCode
     */
    public function __construct(SerialCode $serialCode)
    {
        $this->serialCode = $serialCode;
    }

    /**
     * BeforeToHtml
     *
     * @param template $originalBlock
     */

    public function beforeToHtml(Template $originalBlock)
    {
        $originalBlock->setTemplate('Ooka_OokaSerialNumber::order/view/items/bundle.phtml');
        $originalBlock->setData("view_model", $this->serialCode);
    }
}
