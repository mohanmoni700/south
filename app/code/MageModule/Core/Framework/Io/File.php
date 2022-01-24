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

namespace MageModule\Core\Framework\Io;

/**
 * Class File
 *
 * @package MageModule\Core\Framework\Io
 */
class File extends \Magento\Framework\Filesystem\Io\File
{
    /**
     * @param string $file
     * @param string $mode
     *
     * @return $this
     */
    public function setStream($file, $mode = 'r+')
    {
        $this->_streamFileName = $file;
        $this->_streamChmod    = 0660;
        $this->_streamHandler  = fopen($file, $mode);

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetStream()
    {
        $this->streamClose();
        $this->_streamFileName = null;
        $this->_streamHandler  = null;

        return $this;
    }
}
