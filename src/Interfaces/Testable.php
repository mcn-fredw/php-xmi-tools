<?php
namespace XMITools\Interfaces;

/**
 * Interface Testable
 * Public API for modules that can have tests.
 */
interface Testable
{
    /**
     * Checks the builder has tests.
     * @return true if module has tests, false otherwise.
     * @pre All non-test modules are known to the ModuleStore.
     */
    public function hasTests();
}
