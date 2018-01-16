<?php
namespace XMITools;

class PHPTypeBuilder extends DatatypeBuilder
{
    /**
     * {@inheritDoc}
     */
    public function gatherElements(Interfaces\XMIReader $reader)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function importFor($owning)
    {
        return $this->name();
    }

    /**
     * {@inheritDoc}
     */
    public function isClass()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function typeHintFor($owning)
    {
        return $this->name();
    }
}
