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
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Test\Unit\Model\Data\Validator;

/**
 * Class ValueTest
 *
 * @package MageModule\Core\Test\Unit\Model\Data\Validator
 */
class ValueTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @param array $validValues
     *
     * @return \MageModule\Core\Model\Data\Validator\Value
     */
    private function getValidator(array $validValues)
    {
        $resultFactoryMock = $this->getMockBuilder('MageModule\Core\Model\Data\Validator\ResultFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactoryMock->method('create')->withAnyParameters()->willReturnArgument(0);

        /** @var \MageModule\Core\Model\Data\Validator\Value $validator */
        $validator = $this->objectManager->getObject(
            \MageModule\Core\Model\Data\Validator\Value::class,
            [
                'resultFactory' => $resultFactoryMock,
                'validValues'   => $validValues
            ]
        );

        return $validator;
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        //TODO test multiple value validation
        return [
            // test with integers
            [
                25,
                [25, 'some', 'misc', 'values'],
                true
            ],
            [
                25,
                ['25', 'some', 'misc', 'values'],
                true
            ],
            [
                '25',
                ['25', 'some', 'misc', 'values'],
                true
            ],
            [
                '26',
                ['25', 'some', 'misc', 'values'],
                false
            ],
            // test with decimals
            [
                25.0,
                ['25', 'some', 'misc', 'values'],
                true
            ],
            [
                25.0,
                ['25.00', 'some', 'misc', 'values'],
                true
            ],
            [
                25.0,
                ['25.01', 'some', 'misc', 'values'],
                false,
            ],
            [
                25.0,
                [25.01, 'some', 'misc', 'values'],
                false,
            ],
            // test with null
            [
                null,
                ['25', 'some', 'misc', 'values'],
                false
            ],
            [
                null,
                ['25', 'some', null, 'values'],
                true
            ],
            [
                null,
                ['25', 'some', 'null', 'values'],
                false
            ],
            // test with string
            [
                'string',
                ['25', 'some', 'misc', 'values'],
                false
            ],
            [
                'string',
                ['25', 'some', 'string', 'values'],
                true
            ]
        ];
    }

    /**
     * @param string $value
     * @param string|int|float|bool|null $expected
     *
     * @dataProvider  dataProvider
     */
    public function testValidate($value, $validValues, $expected)
    {
        $result = $this->getValidator($validValues)
            ->validate(is_array($value) ? $value : [$value]);

        $this->assertEquals($expected, $result['isValid']);
    }
}
