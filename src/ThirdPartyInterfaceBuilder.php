<?php
namespace XMITools;

/**
 * Class ThirdPartyInterfaceBuilder
 * Data type builder for 3rd party interfaces.
 */
class ThirdPartyInterfaceBuilder extends DatatypeBuilder
{
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
    public function isInterface()
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
