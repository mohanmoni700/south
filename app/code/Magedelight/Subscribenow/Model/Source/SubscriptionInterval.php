<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package  Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Source;

class SubscriptionInterval extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{

    /**
     * @var \Magedelight\Subscribenow\Helper\Data
     */
    private $helper;
    
    /**
     * @var Array
     */
    private $options = null;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity,
        \Magedelight\Subscribenow\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($eavAttrEntity);
    }

    /**
     * Retrieve all options array.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->options === null) {
            $backSubscriptionInterval = $this->helper->getSubscriptionInterval();
            $this->options[] = ['label' => '--Please select an option--', 'value' => ''];
            
            if (!empty($backSubscriptionInterval)) {
                foreach ($backSubscriptionInterval as $key => $value) {
                    $this->options[] = ['label' => $value['interval_label'], 'value' => $key];
                }
            }
        }
        return $this->options;
    }
}
