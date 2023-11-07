<?php

declare(strict_types=1);

namespace HookahShisha\ChangePassword\Block;

use Alfakher\Productpageb2b\Helper\Data;
use Magento\Framework\View\Element\Template;

class Login extends Template
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data             $helper,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function getUrl($route = '', $params = [])
    {
        $redirectUrl = $this->helper->getConfigValue('login_config/login_success/redirection');

        if (!empty($redirectUrl)) {
            $route = str_replace("{base_url}/", "", $redirectUrl);
        }

        return parent::getUrl($route, $params);
    }
}
