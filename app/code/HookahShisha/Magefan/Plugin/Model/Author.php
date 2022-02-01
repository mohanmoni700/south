<?php

namespace HookahShisha\Magefan\Plugin\Model;

/**
 * Blog author model
 */
class Author
{
    /**
     * Author firstname as name.
     *
     * @param \Magefan\Blog\Model\Author $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetName(\Magefan\Blog\Model\Author $subject, $result)
    {
        return $subject->getFirstname();
    }
}
