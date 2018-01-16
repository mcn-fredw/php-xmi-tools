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
}
