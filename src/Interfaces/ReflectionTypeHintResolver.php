<?php
namespace XMITools\Interfaces;

use ReflectionType;

/**
 * Interface ReflectionTypeHintResolver
 * API for an object that can resolve type hints for
 * a type name from the PHP reflection API.
 */
interface ReflectionTypeHintResolver
{
    /**
     * Resolves a reflection type.
     * @param ModuleStore $store
     * @param ReflectionType $reflection
     * @post Resolver is expected to update the current module's name space
     * to make the returned type hint valid.
     */
    public function resolveReflectionType(
        ModuleStore $store,
        ReflectionType $reflection = null
    );
}
