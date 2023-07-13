<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Ui\Component\Listing\Column\ProductSubscription;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magedelight\Subscribenow\Model\Source\ProfileStatus;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class NextOccurrenceDate extends Column
{
    private $timezone;
    
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ('next_occurrence_date' == $this->getName()) {
                    $item[$this->getName()] = $this->prepareNextOccurrenceDate($item);
                }
            }
        }
        return $dataSource;
    }

    private function prepareNextOccurrenceDate($item = [])
    {
        $status = $item['subscription_status'];
        $date = $item[$this->getName()];
        
        if (!$date || $date == '0000-00-00 00:00:00'
            || $status == ProfileStatus::COMPLETED_STATUS
            || $status == ProfileStatus::CANCELED_STATUS
            || $status == ProfileStatus::SUSPENDED_STATUS
            || $status == ProfileStatus::FAILED_STATUS
        ) {
            return '-';
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }
}
