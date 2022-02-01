<?php
/**
 * Copyright (c) 2019 MageModule, LLC: All rights reserved
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
 * @copyright      2019 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\OrderImportExport\Exception;

use MageModule\OrderImportExport\Helper\Exception;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class ImportException
 *
 * @package MageModule\OrderImportExport\Exception
 */
class ImportException extends LocalizedException
{
    const IMPORT_STATUS_NO  = 0;
    const IMPORT_STATUS_YES = 1;

    /**
     * @var int
     */
    private $imported;

    /**
     * @var string|null
     */
    protected $messagePrefix;

    /**
     * @var array
     */
    protected $stringReplacements = [];

    /**
     * ImportException constructor.
     *
     * @param int        $imported
     * @param Phrase     $phrase
     * @param \Exception $e
     * @param int        $code
     */
    public function __construct(
        $imported,
        Phrase $phrase,
        \Exception $e,
        $code = 0
    ) {
        $this->imported = (int)$imported;
        $phrase         = __($this->constructMessage($e));
        parent::__construct($phrase, $e, $code ? $code : $e->getCode());
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        return $this->imported === self::IMPORT_STATUS_YES;
    }

    /**
     * @return bool
     */
    public function isNotImported()
    {
        return $this->imported === self::IMPORT_STATUS_NO;
    }

    /**
     * @param \Exception $e
     * @param string     $glue
     *
     * @return string
     */
    protected function constructMessage(\Exception $e, $glue = PHP_EOL)
    {
        $prefix  = $this->messagePrefix;
        $message = null;
        if ($e instanceof AbstractAggregateException && $e->getErrors()) {
            $messages = [];

            /** @var LocalizedException $error */
            foreach ($e->getErrors() as $error) {
                $messages[] = $error->getMessage();
            }

            if ($messages) {
                $prefix  = $this->messagePrefix . $e->getMessage();
                $message = implode($glue, $messages);
            }
        }

        $message = $message === null ? $e->getMessage() : $message;
        $message = str_ireplace(
            array_keys($this->stringReplacements),
            array_values($this->stringReplacements),
            $message
        );

        return $prefix . $glue . $message;
    }
}
