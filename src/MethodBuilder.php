<?php
namespace XMITools;

/**
 * Class MethodBuilder
 * Base builder for methods.
 */
abstract class MethodBuilder extends ClassElementBuilder implements
    Interfaces\MethodBuilder
{
    protected $code;
    protected $isAbstract;
    protected $parameters = [];

    /**
     * Callback for saving a method parameter.
     * @param Interfaces\XMIReader $reader Has parameter node.
     * @param Interfaces\Module $builder Pass through is this.
     */
    public function buildParameter(
        Interfaces\XMIReader $reader,
        Interfaces\Module $builder
    ) {
        if ($this->isReturnParameter($reader)) {
            return;
        }
        $parameterFactory = FactoryService::get('method-parameter-builder');
        $parameter = call_user_func($parameterFactory, $reader, $builder);
        $this->parameters[$parameter->name()] = $parameter;
    }

    /**
     * {@inheritDoc}
     */
    public function code()
    {
        return $this->getOrSet('code', ...func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function copy(Interfaces\ClassElementBuilder $source)
    {
        parent::copy($source);
        if ($source instanceof self) {
            $this->isAbstract($source->isAbstract());
            $this->parameters($source->parameters());
            $this->code($source->code());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function importTypes(
        Interfaces\ModuleStore $store,
        Interfaces\TypeHintResolver $resolver
    ) {
        parent::importTypes($store, $resolver);
        foreach ($this->parameters as $parameter) {
            $parameter->importTypes($store, $resolver);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isAbstract()
    {
        return $this->getOrSet('isAbstract', ...func_get_args());
    }

    /**
     * Saves method return type if the reader node
     * represents a method return type.
     * @param Interfaces\XMIReader $reader contains the parameter node.
     */
    protected function isReturnParameter(
        Interfaces\XMIReader $reader
    ) {
        $kind = $reader->attributeValue('kind');
        if ('return' != $kind) {
            return false;
        }
        $this->hint($reader->type());
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function parameters()
    {
        return $this->getOrSet('parameters', ...func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public static function readFromXMI(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleBuilder $builder
    ) {
        $method = parent::readFromXMI($reader, $builder);
        $method->isAbstract($reader->isAbstract());
        $method->code($reader->code());
        $reader->walkParameters([$method, 'buildParameter'], $builder);
        return $method;
    }
}
