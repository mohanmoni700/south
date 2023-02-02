<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Plugin\Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer;

/**
 * @author af_bv_op
 */
use Magento\Backend\Block\Template;

class DefaultRenderer
{
    /**
     * After Get Columns
     *
     * @param Template $originalBlock
     * @param array $after
     * @return array
     */
    public function afterGetColumns(Template $originalBlock, array $after): array
    {
        $after = $after + ['grossmargin' => "col-grossmargin"];
        return $after;
    }
}
