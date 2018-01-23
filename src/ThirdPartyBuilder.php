<?php
namespace XMITools;

/**
 * Class ThirdPartyBuilder
 * Data type builder for 3rd party classes and interfaces.
 */
class ThirdPartyBuilder extends DatatypeBuilder
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
        return $this->fullName();
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
