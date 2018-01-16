<?php
namespace XMITools\Interfaces;

interface ParameterBuilder extends
    TypeImporter,
    XMIReadable
{
    /**
     * Gets doc block for parameter.
     * @return string
     */
    public function docBlock();

    /**
     * Gets name for this parameter.
     * @return string
     */
    public function name();

    /**
     * Gets protottype representation for parameter.
     * @return string
     */
    public function prototype();
}
