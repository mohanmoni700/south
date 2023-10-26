<?php
namespace Zoho\Salesiq\Block;
class InfoBlock extends \Magento\Framework\View\Element\Template
{

    protected $siqModuleHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Zoho\Salesiq\Helper\Data $siqModuleHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_siqmoduleHelper = $siqModuleHelper;
    }

    public function isScriptEmbedEnabled()
    {
        return $this->_siqmoduleHelper->isScriptEmbedEnabled();
    }

    public function getSalesiqScript()
    {
        $zohosalesiq_widget_code = $this->_siqmoduleHelper->getSalesiqScript();
        if(!strpos($zohosalesiq_widget_code, "/widget?plugin_source")){
            $zohosalesiq_widget_code = str_replace("/widget","/widget?plugin_source=magento",$zohosalesiq_widget_code);
        }
        return $zohosalesiq_widget_code;
    }
}
?>
