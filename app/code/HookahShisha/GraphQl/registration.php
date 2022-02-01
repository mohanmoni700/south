<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_GraphQl
 * @author    Janis Verins <info@corra.com
 */
declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'HookahShisha_GraphQl',
    __DIR__
);
