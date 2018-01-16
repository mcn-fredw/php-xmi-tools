<?php
namespace XMITools\Interfaces;

/**
 * Interface AttributeBuilder
 * API for building a class attribute.
 */
interface AttributeBuilder extends ClassElementBuilder
{
    /**
     * Checks attribute is an annotation.
     * @return bool
     */
    public function isAnnotation();

    /**
     * Checks attribute is a constant.
     * @return bool
     */
    public function isConst();

    /**
     * Checks attribute is a test annotation.
     * @return bool
     */
    public function isTest();

    /**
     * Element type value accessor.
     * @return string|$this
     */
    public function value();
}
