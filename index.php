<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;
use UnicoETL\ImportFairsCommand;

/** Load enviroment variables */
$dotenv = new Dotenv();
$dotenv->usePutenv()->load(__DIR__ . '/.env');

/**
 * This registers a new Symfony Console application and binds all
 * our commands to the handler.
 * For more information regarding the ETL process, access each
 * command individually.
 */
$application = new Application();
$application->add(new ImportFairsCommand());
$application->run();
