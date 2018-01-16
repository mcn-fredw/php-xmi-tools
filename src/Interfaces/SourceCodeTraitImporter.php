<?php
namespace XMITools\Interfaces;

use ReflectionClass;

/**
 * Interface SourceCodeTraitImporter
 * Callback API for importing a trait from an existing source file.
 */
interface SourceCodeTraitImporter
{
    /**
     * Asks builder to import a trait from source code.
     * @param ReflectionClass $reflection
     * @param ModuleStore $store
     * @param array &$lines Module file lines.
     */
    public function importSourceCodeTrait(
        ReflectionClass $reflection,
        ModuleStore $store,
        array &$lines
    );
}
