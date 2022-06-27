<?php
declare(strict_types=1);

namespace Alfakher\CustomerCourierAccount\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Get html links for url
 *
 * Class TrackingUrl
 */
class TrackingUrl implements ArgumentInterface
{
   /**
    * Get tracking url
    *
    * @param string $carrier
    * @param string $trackNumber
    * @return string
    **/
    public function getTrackingUrl(string $carrier, string $trackNumber):string
    {
        switch ($carrier) {
            case 'usps':
                $trackingDetail = "https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=".$trackNumber;
                break;
            case 'ups':
                $trackingDetail =
                    "https://www.ups.com/track?loc=null&tracknum=".$trackNumber."&requester=WT/trackdetails";
                break;
            case 'dhl':
                $trackingDetail =
                    "https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id=".$trackNumber;
                break;
            default:
                return $trackNumber;
        }
        return <<<HTML
    <a href="{$trackingDetail}" target="_blank">{$trackNumber}</a>
HTML;
    }
}
