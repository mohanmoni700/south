<?php

namespace HookahShisha\Customization\ViewModel;

use HookahShisha\Customization\Helper\UserData;

/**
 * Provides the user data to fill the form.
 */
class UserDataProvider extends \Magento\Contact\ViewModel\UserDataProvider
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * UserDataProvider constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        UserData $helper
    ) {
        $this->helper = $helper;
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
