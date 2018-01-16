<?php
namespace XMITools\Interfaces;

/**
 * Interface TypeImporter
 * Object that imports type hints.
 */
interface TypeImporter
{
    /**
     * Asks module to resolve data types.
     * @param ModuleStore $store Provided to look up type modules.
     * @param TypeHintResolver $resolver
     * @see XMITools\Interfaces\TypeHintResolver
     * @pre All modules are known to the ModuleStore.
     * @pre Module->getTypeImporter() has been called.
     */
    public function importTypes(
        ModuleStore $store,
        TypeHintResolver $resolver
    );
}
