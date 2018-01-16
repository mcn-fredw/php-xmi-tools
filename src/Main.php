<?php
namespace XMITools;

require_once(__DIR__ . '/../vendor/autoload.php');

class Main
{
    public static function run($ops)
    {
        FactoryService::setFactoriesFromYaml(__DIR__ . '/factories.yaml');
        $collectorFactory = FactoryService::get('module-collector');
        $collector = call_user_func($collectorFactory, $ops);
    }
}

function main($argv)
{
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
