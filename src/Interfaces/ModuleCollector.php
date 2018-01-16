<?php
namespace XMITools\Interfaces;

/**
 * Interface ModuleCollector
 * API for object that builds and writes all modules.
 */
interface ModuleCollector extends ModuleStore
{
    /**
     * Entry point to start creating modules from the UML reader.
     * @param XMIReader $reader Where to get the UML data from.
     */
    public function buildModules(XMIReader $reader);

    /**
     * Asks all the modules to generate tests.
     */
    public function generateTests();

    /**
     * Asks all the modules to map data types.
     */
    public function mapTypes();

    /**
     * Asks all the modules to write to their files.
     */
    public function writeModules();
}
