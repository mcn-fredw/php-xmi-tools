<?php
namespace XMITools\Interfaces;

/**
 * Interface Module
 * Base object for builder to stash data in.
 */
interface Module
{
    /**
     * Builder accessor.
     * @return ModuleBuilder
     */
    public function builder();

    /**
     * Gets fully qualified module name.
     * @return string
     */
    public function fullName();
}
