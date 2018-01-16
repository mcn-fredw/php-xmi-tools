<?php
namespace XMITools\Interfaces;

/**
 * Interface MethodBuilder
 * Interface for building a method.
 */
interface MethodBuilder extends ClassElementBuilder
{
    /**
     * Method code accessor.
     * @return string|$this
     */
    public function code();

    /**
     * Method is abstract accessor.
     * @return bool|$this
     */
    public function isAbstract();

    /**
     * Method parameters accessor.
     * @return array|$this
     */
    public function parameters();
}
