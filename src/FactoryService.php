<?php
namespace XMITools;

use Symfony\Component\Yaml\Yaml;
use OutOfBoundsException;

/**
 * Class FactoryService
 * Provides common place to get factories.
 */
class FactoryService
{
    /** Name to factory look up. */
    protected static $factories = [];

    /**
     * Sets a list of factories.
     * @param array &$list List of name => factory pairs.
     */
    public static function setFactories(&$list)
    {
        foreach ($list as $name => $factory) {
            static::setFactory($name, $factory);
        }
    }

    /**
     * Sets a list of factories from yaml file.
     * @param string path Path to yaml file.
     */
    public static function setFactoriesFromYaml($path)
    {
        $list = Yaml::parse(file_get_contents($path));
        static::setFactories($list);
    }

    /**
     * Sets the factory callback for name.
     * @param string $name A unique name for the factory.
     * @param mixed $factory Something to use to create instances for name.
     */
    public static function setFactory($name, $factory)
    {
        static::$factories[$name] = $factory;
    }

    /**
     * Gets the factory for name.
     * @param string $name Configured name for factory.
     * @return null|string Used to create instances for name.
     * null if name is unknown,
     */
    public static function get($name)
    {
        if (isset(static::$factories[$name])) {
            return static::$factories[$name];
        }
        $msg = "$name is an unknown factory";
        throw new OutOfBoundsException($msg);
    }
}
