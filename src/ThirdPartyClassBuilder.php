<?php
namespace XMITools;

/**
 * Class ThirdPartyClassBuilder
 * Data type builder for 3rd party classes.
 */
class ThirdPartyClassBuilder extends ThirdPartyInterfaceBuilder
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
