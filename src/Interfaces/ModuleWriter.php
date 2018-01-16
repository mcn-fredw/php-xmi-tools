<?php
namespace XMITools\Interfaces;

/**
 * Interface ModuleWriter
 * Public API for modules that write modules.
 */
interface ModuleWriter
{
    /**
     * Writes module.
     * @param Interfaces\ModuleStore $store
     * @param PathTranslator $paths Translates module full name
     * to file system path.
     */
    public function writeModule(
        ModuleStore $store,
        PathTranslator $paths
    );
}
