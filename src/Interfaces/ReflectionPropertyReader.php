<?php
namespace XMITools\Interfaces;

use ReflectionProperty;

/**
 * Interface ReflectionPropertyReader
 * API for an object that can be updated from a ReflectionProperty.
 */
interface ReflectionPropertyReader
{
    /**
     * Updates object from a reflection property.
     * @param ReflectionProperty $reflection
     * @param array &$defaults Default property values.
     * @param ReflectionTypeHintResolver $resolver
     * Makes sure type hint is in module name space.
     * @param ModuleStore $store Needed by resolver.
     * @param arrary $lines Module file as an array of lines.
     */
    public function readFromReflectionProperty(
        ReflectionProperty $reflection,
        array &$defaults,
        ReflectionTypeHintResolver $resolver,
        ModuleStore $store,
        array &$lines
    );
}
