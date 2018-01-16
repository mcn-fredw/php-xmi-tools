<?php
namespace XMITools\Traits;

/**
 * Trait Accessor
 * Generic getter/setter sub.
 */
trait Accessor
{
    /**
     * Gets or sets class attribute.
     * @param string name Object attribute name.
     * @param ... $value Setter value.
     * @return mixed Object attribute value for getter.
     * Object ($this) for setter.
     */
    protected function getOrSet($name)
    {
        if (func_num_args() == 1) {
            return $this->{$name};
        }
        $this->{$name} = func_get_arg(1);
        return $this;
    }
}
