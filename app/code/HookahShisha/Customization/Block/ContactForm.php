<?php

namespace HookahShisha\Customization\Block;

use Magento\Framework\View\Element\Template;
use HookahShisha\Customization\Helper\UserData;

/**
 * Main contact form block
 *
 * @api
 * @since 100.0.2
 */
class ContactForm extends Template
{

    /**
     * @param Template\Context $context
     * @param UserData $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        UserData $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->_isScopePrivate = true;
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('contact/index/post', ['_secure' => true]);
    }

    /**
     * Get user first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->helper->getPostValue('first_name') ?: $this->helper->getFirstName();
    }

    /**
     * Get user last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->helper->getPostValue('last_name') ?: $this->helper->getLastName();
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->helper->getPostValue('email') ?: $this->helper->getUserEmail();
    }

    /**
     * Get user telephone
     *
     * @return string
     */
    public function getUserTelephone()
    {
        return $this->helper->getPostValue('telephone');
    }

    /**
     * Get user tax id
     *
     * @return string
     */
    public function getTaxId()
    {
        return $this->helper->getPostValue('tax_id');
    }

    /**
     * Get tobacco permit number
     *
     * @return string
     */
    public function getTobaccoPermitNumber()
    {
        return $this->helper->getPostValue('tobacco_permit_number');
    }

    /**
     * Get issue type
     *
     * @return string
     */
    public function getIssueType()
    {
        return $this->helper->getPostValue('issue_type');
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->helper->getPostValue('subject');
    }

    /**
     * Get user comment
     *
     * @return string|null
     */
    public function getUserComment()
    {
        return $this->helper->getPostValue('comment');
    }
}
