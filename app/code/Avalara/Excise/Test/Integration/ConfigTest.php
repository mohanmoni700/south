<?php
/**
 * Avalara_Excise
 *
 */

namespace Avalara\Excise\Test\Integration;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    private $moduleName = 'Avalara_Excise';

    public function testModuleIsRegistered()
    {
        /**
         * @var ComponentRegistrar $registrar
         */
        $registrar = new ComponentRegistrar();

        $this->assertArrayHasKey($this->moduleName, $registrar->getPaths(ComponentRegistrar::MODULE));
    }

    public function testModuleIsConfiguredAndEnabled()
    {
        /**
         * @var ObjectManager $objectManager
         */
        $objectManager = ObjectManager::getInstance();

        /**
         * @var ModuleList $moduleList
         */
        $moduleList = $objectManager->create(ModuleList::class);

        $this->assertTrue($moduleList->has($this->moduleName));
    }
}
