<?php
namespace XMITools\Interfaces;

/**
 * Interface TypeHintResolver
 * Object that can resolve a type hint.
 */
interface TypeHintResolver
{
    /**
     * Asks module to resolve data types.
     * @param ModuleStore $store Provided to look up type modules.
     * @param string $typeXmiId XML id of data type to lookup.
     * @return string Type hint. Empty string if type is unknown.
     * @pre All modules are known to the ModuleStore.
     * @pre Module->getTypeImporter() has been called.
     */
    public function resolveTypeHint(ModuleStore $store, $typeXmiId);
}
