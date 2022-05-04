<?php

namespace Alfakher\Blog\Block\Sidebar;

/**
 * Alfakher Blog sidebar archive block
 */
class Archive extends \Magefan\Blog\Block\Sidebar\Archive
{

    /**
     * Retrieve available months
     *
     * @return array
     */
    public function getMonths()
    {
        if (null === $this->_months) {
            $this->_months = [];
            $this->_preparePostCollection();
            foreach ($this->_postCollection as $post) {
                $time = strtotime($post->getData('publish_time'));
                $this->_months[date('Y', $time)][date('Y-m', $time)] = $time;
            }
        }

        return $this->_months;
    }
}
