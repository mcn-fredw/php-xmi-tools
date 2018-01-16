<?php
namespace XMITools;

use ReflectionClassConstant;

/**
 * Class TraitBuilder
 * Module builder for a trait.
 */
class TraitBuilder extends ClassBuilder
{
    const TYPE_NAME = 'trait';

    /**
     * {@inheritDoc}
     */
    public function bindAbstraction(Interfaces\Module $supplier)
    {
        $id = $supplier->xmiId();
        $import = $supplier->importFor($this->ns);
        $hint = $supplier->typeHintFor($this->ns);
        /*
         * allows interface(s) to define methods for latter import.
         * don't set actual "use" import, traits don't "implement"
         */
        $this->bindAbstractionData(
            $supplier->isInterface(),
            $this->implements,
            $id,
            '',
            $hint
        );
        /* allows "use" of other trait(s) */
        $this->bindAbstractionData(
            $supplier->isTrait(),
            $this->traits,
            $id,
            $import,
            $hint
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isClass()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isTrait()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function moduleDeclaration()
    {
        return (array)$this->moduleTypeAndName();
    }
}
