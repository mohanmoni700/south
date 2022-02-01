<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Helper\Db;

/**
 * Class Distance
 *
 * @package MageModule\Core\Helper\Db
 */
class Distance extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_COL_LATITUDE  = 'latitude';
    const DEFAULT_COL_LONGITUDE = 'longitude';
    const UNIT_MILES            = 'miles';
    const UNIT_KILOMETERS       = 'kilometers';
    const FACTOR_MILES          = '69.0';
    const FACTOR_KILOMETERS     = '111.045';

    /**
     * @param string $units
     *
     * @return string
     */
    public function getCalcFactor($units)
    {
        return $units === 'miles' ? self::FACTOR_MILES : self::FACTOR_KILOMETERS;
    }

    /**
     * @param float|string $latitude
     * @param float|string $longitude
     * @param string       $units
     * @param string       $latitudeField
     * @param string       $longitudeField
     *
     * @return string
     */
    public function generateSql(
        $latitude,
        $longitude,
        $units = self::FACTOR_KILOMETERS,
        $latitudeField = self::DEFAULT_COL_LATITUDE,
        $longitudeField = self::DEFAULT_COL_LONGITUDE
    ) {
        $sql = "({$this->getCalcFactor($units)} * DEGREES(ACOS(COS(RADIANS({$latitude})) ";
        $sql .= "* COS(RADIANS({$latitudeField})) ";
        $sql .= "* COS(RADIANS({$longitude}) - RADIANS({$longitudeField})) ";
        $sql .= "+ SIN(RADIANS({$latitude})) ";
        $sql .= "* SIN(RADIANS({$latitudeField})))))";

        return $sql;
    }

    /**
     * @param string $units
     *
     * @return bool
     */
    public function validateUnits($units)
    {
        return in_array($units, [self::UNIT_KILOMETERS, self::UNIT_MILES]);
    }
}
