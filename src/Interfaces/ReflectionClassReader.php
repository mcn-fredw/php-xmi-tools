<?php
namespace XMITools\Interfaces;

use ReflectionClass;

/**
 * Interface ReflectionClassReader
 * API for an object that can be updated from a ReflectionClass.
 */
interface ReflectionClassReader
{
    /**
     * Updates object from a reflection class.
     * @param ReflectionClass $reflection
     * @param ReflectionTypeHintResolver $resolver
     * Makes sure type hint is in module name space.
     * @param ModuleStore $store Needed by resolver.
     * @param arrary &$lines Module file as an array of lines.
     */
    public static function readFromReflectionClass(
        ReflectionClass $reflection,
        ReflectionTypeHintResolver $resolver,
        ModuleStore $store,
        array &$lines
    );
}
