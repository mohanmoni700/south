<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Avalara_Excise',
    __DIR__
);

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Avalara_BaseProvider',
    __DIR__ . DIRECTORY_SEPARATOR . 'BaseProvider'
);
