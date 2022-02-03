<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright    2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Test\Unit;

abstract class AbstractTestCase extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * Allows us to unit test private/protected methods
     *
     * @param object $object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Converts numeric and null to float
     *
     * @param array $data
     */
    public function floatify(array &$data)
    {
        foreach ($data as &$value) {
            if (is_numeric($value) || $value === null) {
                $value = (float)$value;
            }
        }
    }
}
