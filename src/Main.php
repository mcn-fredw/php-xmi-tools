<?php
namespace XMITools;

use RunTimeException;

require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Startup object.
 */
class Main
{
    public static function run($ops)
    {
        FactoryService::setFactoriesFromYaml(__DIR__ . '/factories.yaml');
        $collectorFactory = FactoryService::get('module-collector');
        $collector = call_user_func($collectorFactory, $ops);
    }
}

/**
 * Convert notices to exceptions.
 */
function noticeToException($errno, $errString)
{
    throw new RunTimeException($errString);
}

/**
 * Entry point.
 */
function main($argv)
{
    set_error_handler('XMITools\noticeToException', E_NOTICE);
    Main::run(
        getopt(
            '',
            [
                'project-dir:',
                'xmi-file:'
            ]
        )
    );
}

main($argv);
