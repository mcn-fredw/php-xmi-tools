<?php
namespace XMITools\Interfaces;

/**
 * Interface MethodBuilder
 * Interface for building a method.
 */
interface ClassElementBuilder extends
    TypeImporter,
    XMIReadable
{
    /**
     * Element comment accessor.
     * @return string|$this
     */
    public function comment();

    /**
     * Copy element for source.
     * @param ClassElementBuilder $source
     */
    public function copy(ClassElementBuilder $source);

    /**
     * Element type hint accessor.
     * @return string|$this
     */
    public function hint();

    /**
     * Element is static accessor.
     * @return bool|$this
     */
    public function isStatic();

    /**
     * Element name accessor.
     * @return string|$this
     */
    public function name();

    /**
     * Gets source code lines for element.
     * @return array
     */
    public function sourceCode();

    /**
     * Element visibility accessor.
     * @return string|$this
     */
    public function visibility();

    /**
     * Element xmiId accessor.
     * @return string|$this
     */
    public function xmiId();
}
