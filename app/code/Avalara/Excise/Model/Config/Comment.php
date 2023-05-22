<?php

namespace Avalara\Excise\Model\Config;

use Magento\Config\Model\Config\CommentInterface;

use Avalara\Excise\Framework\Constants;

class Comment implements CommentInterface
{
    /**
     * Set version of extension to admin configuration
     *
     * @param $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        return sprintf('<p><strong>%s</strong></p>', Constants::APP_VERSION);
    }
}
