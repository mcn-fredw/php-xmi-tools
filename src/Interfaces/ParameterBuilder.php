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
     * Gets type hint for this parameter.
     * @return string
     */
    public function hint();

    /**
     * Gets type hint xmi id for this parameter.
     * @return string
     */
    public function hintXmi();

    /**
     * Gets protottype representation for parameter.
     * @return string
     */
    public function prototype();
}
