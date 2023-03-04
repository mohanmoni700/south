<?php

declare(strict_types=1);

namespace HookahShisha\Catalog\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\Collection;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Options extends AbstractSource
{
    /**
     * @var Collection
     */
    private Collection $cmsBlockcollection;

    /**
     * @param Collection $cmsBlockcollection
     */
    public function __construct(
        Collection $cmsBlockcollection
    )
    {
        $this->cmsBlockcollection = $cmsBlockcollection;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if(!$this->_options)
        {
            $result = $this->cmsBlockcollection->getData();
            $this->_options=[ ['label'=>'Select Options', 'value'=>'']];
            foreach($result as $s)
            {
                $this->_options[] = array('value' => $s['identifier'], 'label' => $s['title']);
            }
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
