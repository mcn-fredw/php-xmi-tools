<?php
namespace XMITools;

/**
 * Class Module
 * Basic Interfaces\Module implementation.
 */
class Module implements Interfaces\Module
{
    protected $isAbstract;
    protected $name;
    protected $ns;
    protected $stereotype;
    protected $xmiId;

    /**
     * Generic accessor.
     * @param string $method What property to get/set.
     */
    public function __call($method, $args)
    {
        if (0 == count($args)) {
            return $this->{$method};
        }
        $this->{$method} = reset($args);
        return $this;
    }

    /**
     * Generic property setter.
     * @param string $name What property to set.
     * @param mixed $value What value to set property to.
     */
    public function __get($name)
    {
        return $this->{$name};
    }

    /**
     * Generic property setter.
     * @param string $name What property to set.
     * @param mixed $value What value to set property to.
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function builder()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function fullName()
    {
        return sprintf('%s\%s', $this->ns, $this->name);
    }
}
