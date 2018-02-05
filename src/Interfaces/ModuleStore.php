<?php
namespace XMITools\Interfaces;

interface ModuleStore
{
    /**
     * Look up a module by full name.
     * @param string $fullName Full name of module to look up.
     * @return null|Module
     */
    public function findModule($fullName);

    /**
     * Look up a module by MXI id.
     * @param string $id XMI id of module.
     * @return Module
     */
    public function getModule($id);

    /**
     * Gets a name space import name for a type hint.
     * @param string $ns Calling module's name space.
     * @param string $hint Type hint to look up.
     * @return string
     */
    public function importFor($ns, $hint);

    /**
     * Saves a modules to the module store.
     * @param $module The module to store.
     */
    public function saveModule(Module $module);
}
