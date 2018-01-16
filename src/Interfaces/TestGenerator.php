<?php
namespace XMITools\Interfaces;

/**
 * Interface TestGenerator
 * Public API for generating a test module for another module.
 */
interface TestGenerator
{
    /**
     * Generates tests for module.
     * @param ModuleStore $store Where to store the test module.
     * @pre All non-test modules are known to the ModuleStore.
     * @pre Module->getTestGenerator() has been called.
     */
    public function generateTests(ModuleStore $store);
}
