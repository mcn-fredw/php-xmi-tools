<?php
namespace XMITools\Interfaces;

use ReflectionMethod;

/**
 * Interface SourceCodeMethodImporter
 * Callback API for importing a method from an existing source file.
 */
interface SourceCodeMethodImporter
{
    /**
     * Asks builder to import a method from source code.
     * @param ReflectionMethod $reflection
     * @param ModuleStore $store
     * @param array &$lines File lines.
     */
    public function importSourceCodeMethod(
        ReflectionMethod $reflection,
        ModuleStore $store,
        array &$lines
    );
}
