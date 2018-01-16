<?php
namespace XMITools\Interfaces;

use ReflectionProperty;

/**
 * Interface SourceCodeAttributeImporter
 * Callback API for importing attributes from an existing source file.
 */
interface SourceCodeAttributeImporter
{
    /**
     * Asks builder to import an attribute from source code.
     * @param ReflectionProperty $reflection
     * @param array &$defaults Default property values.
     * @param ModuleStore $store
     * @param array &$lines
     */
    public function importSourceCodeAttribute(
        ReflectionProperty $reflection,
        array &$defaults,
        ModuleStore $store,
        array &$lines
    );
}
