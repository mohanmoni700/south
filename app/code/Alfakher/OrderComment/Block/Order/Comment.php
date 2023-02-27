<?php
namespace Alfakher\OrderComment\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Comment extends \Magento\Framework\View\Element\Template
{
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param array $data
     */

    public function __construct(
        TemplateContext $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->_template = 'order/view/comment.phtml';
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
    /**
     * @inheritDoc
     */
    public function getOrderComment()
    {
        return $this->getOrder()->getData('order_comment');
    }
}
