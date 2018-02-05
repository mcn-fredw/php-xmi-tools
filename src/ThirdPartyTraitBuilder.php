<?php
namespace XMITools;

/**
 * Class ThirdPartyTraitBuilder
 * Data type builder for 3rd party traits.
 */
class ThirdPartyTraitBuilder extends ThirdPartyClassBuilder
{
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
}
