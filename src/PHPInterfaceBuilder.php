<?php
namespace XMITools;

/**
 * Class PHPInterfaceBuilder
 * Data type builder for PHP/Pear/Pecl interfaces.
 */
class PHPInterfaceBuilder extends DatatypeBuilder
{
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
    public function isInterface()
    {
        return true;
    }
}
