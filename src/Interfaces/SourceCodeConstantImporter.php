<?php
namespace XMITools\Interfaces;

use ReflectionClassConstant;

/**
 * Interface SourceCodeConstantImporter
 * Callback API for importing constants from an existing source file.
 */
interface SourceCodeConstantImporter
{
    /**
     * Asks builder to add constant read from source code.
     * @param string $name Constant name.
     * @param string $value Constant value.
     * @param ModuleStore $store
     */
    public function importSourceCodeConstant(
        ReflectionClassConstant $reflection,
        ModuleStore $store
    );
}
