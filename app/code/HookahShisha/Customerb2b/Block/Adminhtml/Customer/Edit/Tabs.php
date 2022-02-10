<?php
namespace HookahShisha\Customerb2b\Block\Adminhtml\Customer\Edit;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block
 */
class Tabs extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Gget current customer ID
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Return customer repo
     *
     * @return string|null
     */
    public function getCustomerdata()
    {
        $customerId = $this->getCustomerId();
        return $this->_customerRepositoryInterface->getById($customerId);
    }

    /**
     * Tab Label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Other Contact detail');
    }

    /**
     * Tab Title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Other Contact detail');
    }

    /**
     * Show Tab
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
     * Hidden
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
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Ajax Loaded
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Form
     *
     * @return mixed
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('myform_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Other Contact Information')]);
        $contactdata = $this->getCustomerdata();

        $contact_name = $contactdata->getCustomAttribute('contact_name');
        $contactname = $contact_name ? $contact_name->getValue() : null;

        $contact_phone = $contactdata->getCustomAttribute('contact_phone');
        $contactphone = $contact_phone ? $contact_phone->getValue() : null;

        $contact_email = $contactdata->getCustomAttribute('contact_email');
        $contactemail = $contact_email ? $contact_email->getValue() : null;

        $fieldset->addField(
            'contact_name',
            'text',
            [
                'name' => 'customer[contact_name]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Contact Name'),
                'title' => __('Contact Name'),
                'value' => $contactname,
            ]
        );
        $fieldset->addField(
            'contact_phone',
            'text',
            [
                'name' => 'customer[contact_phone]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Contact Phone'),
                'title' => __('Contact Phone'),
                'value' => $contactphone,
            ]
        );
        $fieldset->addField(
            'contact_email',
            'text',
            [
                'name' => 'customer[contact_email]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Contact Email'),
                'title' => __('Contact Email'),
                'value' => $contactemail,
            ]
        );
        $this->setForm($form);
        return $this;
    }

    /**
     * Html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
