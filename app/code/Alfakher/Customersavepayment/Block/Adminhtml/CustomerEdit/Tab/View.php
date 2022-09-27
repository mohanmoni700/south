<?php
namespace Alfakher\Customersavepayment\Block\Adminhtml\CustomerEdit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Vault\Model\CreditCardTokenFactory;

class View extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * [__construct]
     *
     * @param Context $context
     * @param Registry $registry
     * @param CreditCardTokenFactory $creditCardTokenFactory
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CreditCardTokenFactory $creditCardTokenFactory,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->creditCardTokenFactory = $creditCardTokenFactory;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * [getCustomerId]
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * [getTabLabel]
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Saved Payment Cards');
    }

    /**
     * [getTabTitle]
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Saved Payment Cards');
    }

    /**
     * [canShowTab]
     *
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * [isHidden]
     *
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * [Tab class getter]
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * [Return URL link to Tab content]
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('customercredithistory/customer/index', ['_current' => true]);
    }

    /**
     * [Tab should be loaded trough Ajax call]
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
}
