<?php
namespace XMITools\Interfaces;

use ReflectionClassConstant;

/**
 * Interface RefelectionConstantReader
 * API for an object that can be updated from a ReflectionClassConstant.
 */
interface RefelectionConstantReader
{
    /**
     * Updates object from a reflection property.
     * @param ReflectionClassConstant $reflection
     * @param ReflectionTypeHintResolver $resolver
     * Makes sure type hint is in module name space.
     * @param ModuleStore $store Needed by resolver.
     */
    public function readFromReflectionConstant(
        ReflectionClassConstant $reflection,
        ReflectionTypeHintResolver $resolver,
        ModuleStore $store
    );
}
