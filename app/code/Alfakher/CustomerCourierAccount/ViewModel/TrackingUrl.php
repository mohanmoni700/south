<?php

declare(strict_types=1);

namespace Alfakher\CustomerCourierAccount\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use \Magento\Sales\Model\Order\Shipment\Track as TrackInfo;

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
    * @param TrackInfo $trackingInfo
    * @return string|null
    **/
    public function getTrackingUrl(TrackInfo $trackingInfo): ?string
    {
        [$trackingDetail, $trackLable, $trackId] = $this->prepareTrackingUrl($trackingInfo);

        return <<<HTML
    <a href="{$trackingDetail}" target="_blank">{$trackLable}</a>{$trackId}
HTML;
    }

    /**
     * Get tracking URL
     *
     * @param TrackInfo $trackingInfo
     * @return null|string|false
     */
    public function getTrackingUrlString(TrackInfo $trackingInfo)
    {
        [$trackingDetail] = $this->prepareTrackingUrl($trackingInfo);

        if (empty($trackingDetail)) {
            return false;
        }

        return $trackingDetail;
    }

    /**
     * Prepare shipment tracking info.
     *
     * @param TrackInfo $trackingInfo
     * @return array|string|null
     */
    private function prepareTrackingUrl(TrackInfo $trackingInfo)
    {
        $carrier = strtolower($trackingInfo->getCarrierCode());
        $trackNumber = $trackingInfo->getTrackNumber();
        if (!$trackNumber) {
            return null;
        }

        $trackLable = __("Track order");
        $trackId = __(" , Tracking id - %1", $trackNumber);
        if ($carrier === 'custom') {
            $carrier = strtolower((string)$trackingInfo->getTitle());
        }
        switch ($carrier) {
            case 'usps':
                $trackingDetail = "https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=" . $trackNumber;
                break;
            case 'ups':
                $trackingDetail =
                    "https://www.ups.com/track?loc=null&tracknum=" . $trackNumber . "&requester=WT/trackdetails";
                break;
            case 'dhl':
                $trackingDetail =
                    "https://www.dhl.com/us-en/home/tracking/tracking-express.html?submit=1&tracking-id="
                    . $trackNumber;
                break;
            default:
                return $trackNumber;
        }

        return [$trackingDetail, $trackLable, $trackId];
    }
}
