<?php
namespace XMITools;

class DatatypeBuilder extends BaseModuleBuilder
{
    /**
     * {@inheritDoc}
     */
    public function gatherElements(
        Interfaces\XMIReader $reader
    ) {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function importFor(
        $owning
    ) {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function typeHintFor(
        $owning
    ) {
        return $this->name();
    }
}
