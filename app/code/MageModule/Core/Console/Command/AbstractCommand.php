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

namespace MageModule\Core\Console\Command;

/**
 * Class AbstractCommand
 *
 * @package MageModule\Core\Console\Command
 */
abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * AbstractCommand constructor.
     *
     * @param array       $defaultOptions
     * @param null|string $name
     */
    public function __construct(
        $defaultOptions = [],
        $name = null
    ) {
        $this->defaultOptions = $defaultOptions;
        parent::__construct($name);
    }

    /**
     * @param string $option
     *
     * @return string|null
     */
    public function getDefaultOption($option)
    {
        return array_key_exists($option, $this->defaultOptions) ?
            $this->defaultOptions[$option] :
            null;
    }
}
