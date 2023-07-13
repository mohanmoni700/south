<?php
/**
 * Magedelight
 * Copyright (C) 2022 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Block\Customer\Account\Profile;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;


class Toolbar extends Template
{

    private $urlInterface;
    public $profileStatus;

    public function __construct(
        Context $context,
        UrlInterface $urlInterface,
        ProfileStatus $profileStatus
    ) {
        $this->profileStatus = $profileStatus;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    public function getProfileStatus()
    {
        return $this->profileStatus->getOptions();
    }

    public function getCurrentUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }

}