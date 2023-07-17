<?php

declare(strict_types = 1);

namespace Alfakher\Tabby\Model;

class TabbySession
{
    /**
     * @var string
     */
    private $tabbyRedirectUrl;

    /**
     * Get Tabby Redirect URL
     *
     * @return string
     */
    public function getTabbyRedirectUrl()
    {
        return $this->tabbyRedirectUrl;
    }

    /**
     * Set Tabby Redirect URL
     *
     * @param string $tabbyRedirectUrl
     * @return void
     */
    public function setTabbyRedirectUrl($tabbyRedirectUrl)
    {
        $this->tabbyRedirectUrl = $tabbyRedirectUrl;
    }
}
