<?php

declare(strict_types=1);

namespace UnicoETL\Helpers;

use Exception;

/**
 * Class Logger
 * Implements a simple interface for outputting logs on a file.
 *
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
final class Logger
{
    /**
     * The file name to output any application logs.
     * 
     * @var string
     */
    protected const FILENAME = 'log.txt';

    /** 
     * Outputs a message to the log file.
     * 
     * @return void
     */
    public static final function Error(Exception $exception): void
    {
        $dateTime = date(format: "Y-m-d H:i:s");;
        $message = $exception->getMessage();
        $stack = $exception->getTraceAsString();

        $errorText = <<<EOD
        ================== EXCEPTION ==================
        DATETIME: $dateTime
        MESSAGE: $message
        STACKTRACE:
        $stack
        \n\n\n
        EOD;

        file_put_contents(filename: self::FILENAME, data: $errorText, flags: FILE_APPEND | LOCK_EX);
    }
}
