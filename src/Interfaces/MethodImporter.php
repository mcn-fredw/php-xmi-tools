<?php
namespace XMITools\Interfaces;

/**
 * Interface MethodImporter
 * Public API for modules that import methods from abstractions.
 */
interface MethodImporter
{
    /**
     * Called to import methods from bound abstractions.
     * @param ModuleStore $store Provided to look up the
     * the abstractions bound to this module.
     * @pre All modules are known to the ModuleStore.
     * @pre Module->getMethodImporter() has been called.
     */
    public function importMethods(ModuleStore $store);
}
