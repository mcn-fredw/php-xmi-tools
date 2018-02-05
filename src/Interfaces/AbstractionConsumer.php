<?php
namespace XMITools\Interfaces;

/**
 * Interface AbstractionConsumer
 * Public API for modules that consume abstractions.
 */
interface AbstractionConsumer
{
    /**
     * Binds an abstraction to this module.
     * @param Module $supplier Module that represents the abstraction.
     */
    public function bindAbstraction(Module $supplier);

    /**
     * Finds method by name in imported method containers.
     * @param string $methodKey
     * @param Interfaces\ModuleStore $store
     * @return null|Interfaces\MethodBuilder
     */
    public function findImportedMethod(
        $methodKey,
        ModuleStore $store
    );

    /**
     * Finds method by name.
     * @param string $methodKey
     * @param Interfaces\ModuleStore $store
     * @param bool $recurce Flags scan related method containers.
     * @return null|Interfaces\MethodBuilder
     */
    public function findMethod(
        $methodKey,
        ModuleStore $store,
        $recurse = true
    );
}
