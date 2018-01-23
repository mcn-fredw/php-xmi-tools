<?php
namespace XMITools;

/**
 * Class PHPClassBuilder
 * Data type builder for PHP/Pear/Pecl classes.
 */
class PHPClassBuilder extends PHPInterfaceBuilder
{
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
    public function isInterface()
    {
        return false;
    }
}
