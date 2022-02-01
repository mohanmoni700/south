<?php

namespace MageModule\Core\Helper;

/**
 * Class Time
 *
 * @package MageModule\Core\Helper
 */
class Time extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param int $seconds
     *
     * @return array
     */
    public function secondsToDaysHoursMinutes($seconds)
    {
        if ($seconds) {
            $minutes = floor($seconds / 60);
        } else {
            $minutes = 0;
        }

        return $this->minutesToDaysHoursMinutes($minutes);
    }

    /**
     * Returns data so it can be used to display a countdown such as 15 days 4 hours 3 minutes
     *
     * @param int $minutes
     *
     * @return array
     */
    public function minutesToDaysHoursMinutes($minutes)
    {
        if ($minutes) {
            $days    = floor($minutes / 1440);
            $hours   = floor(($minutes - $days * 1440) / 60);
            $minutes = $minutes - ($days * 1440) - ($hours * 60);
        } else {
            $days = 0;
            $hours = 0;
            $minutes = 0;
        }

        return [
            'days'    => $days,
            'hours'   => $hours,
            'minutes' => $minutes
        ];
    }
}
