<?php
namespace Avalara\Excise\Helper\Rest;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Avalara\Excise\Api\RestInterface;

class Config extends AbstractHelper
{
    const C_SHIPFROM = "ShipFrom";
    const C_SHIPTO = "ShipTo";
    const C_MIXED = 1;
    const C_SUCCESS = 0;
    const C_WARNING = 1;
    const C_ERROR = 2;
    const C_EXCEPTION = 3;
    const C_ADDRESS_SUCCESS = "Success";
    const C_ADDRESS_WARNING = "Warning";
    const C_ADDRESS_ERROR = "Error";
    const C_ADDRESS_EXCEPTION = "Exception";
    /**
     * @param Context $context
     * @param RestInterface $restInteraction
     */
    public function __construct(
        Context $context,
        RestInterface $restInteraction
    ) {
        parent::__construct($context);

        /**
         * This statement MUST be here, so that all classes imported by the AvaTaxClient file will be loaded
         */
        $restInteraction->getClient();
    }

    /**
     * @return string
     */
    public function getAddrTypeFrom()
    {
        return self::C_SHIPFROM;
    }

    /**
     * @return string
     */
    public function getAddrTypeTo()
    {
        return self::C_SHIPTO;
    }
    /**
     * @return string
     */
    public function getTextCaseMixed()
    {
        return self::C_MIXED;
    }
    /**
     * @return array
     */
    public function getErrorSeverityLevels()
    {
        return [
            self::C_ERROR,
            self::C_EXCEPTION,
        ];
    }
    /**
     * @return array
     */
    public function getWarningSeverityLevels()
    {
        return [
            self::C_WARNING,
        ];
    }
    /**
     * @return array
     */
    public function getAddressErrorSeverityLevels()
    {
        return [
            self::C_ADDRESS_ERROR,
            self::C_ADDRESS_EXCEPTION,
        ];
    }
    /**
     * @return array
     */
    public function getAddressWarningSeverityLevels()
    {
        return [
            self::C_ADDRESS_WARNING,
        ];
    }
}
