<?php
namespace XMITools\Interfaces;

use ReflectionMethod;

/**
 * Interface ReflectionMethodReader
 * API for an object that can be updated from a ReflectionMethod.
 */
interface ReflectionMethodReader
{
    /**
     * Updates object from a reflection method.
     * @param ReflectionMethod $reflection
     * @param ReflectionTypeHintResolver $resolver
     * Makes sure type hint is in module name space.
     * @param ModuleStore $store Needed by resolver.
     * @param arrary &$lines Module file as an array of lines.
     */
    public function readFromReflectionMethod(
        ReflectionMethod $reflection,
        ReflectionTypeHintResolver $resolver,
        ModuleStore $store,
        array &$lines
    );
}
