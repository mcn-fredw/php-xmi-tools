<?php
namespace XMITools\Interfaces;

/**
 * Interface TemplateParameterConsumer
 * Builder object that consumes template parameters.
 */
interface TemplateParameterConsumer
{
    /**
     * Binds a template parameter to this module.
     * @param XMIReader $reader UML reader with the current node
     * set to the template parameter node.
     * @param Module $builder Typically $this.
     */
    public function buildTemplateParameter(
        XMIReader $reader,
        Module $builder
    );
}
