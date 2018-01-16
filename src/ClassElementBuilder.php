<?php
namespace XMITools;

/**
 * Class ClassElementBuilder
 * Base builder for class elements (attributes and methods).
 */
abstract class ClassElementBuilder implements
    Interfaces\ClassElementBuilder
{
    use Traits\Accessor;

    const TAB = CodeFormatter::TAB;
    const MAX_LINE_LENGTH = CodeFormatter::MAX_LINE_LENGTH;

    protected $comment;
    protected $hint;
    protected $isStatic;
    protected $name;
    protected $visibility;
    protected $xmiId;

    /**
     * {@inheritDoc}
     */
    public function comment()
    {
        return $this->getOrSet('comment', ...func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function copy(Interfaces\ClassElementBuilder $source)
    {
        $this->comment($source->comment());
        $this->hint($source->hint());
        $this->isStatic($source->isStatic());
        $this->name($source->name());
        $this->visibility($source->visibility());
        $this->xmiId($source->xmiId());
    }

    /**
     * {@inheritDoc}
     */
    public function hint()
    {
        return $this->getOrSet('hint', ...func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function importTypes(
        Interfaces\ModuleStore $store,
        Interfaces\TypeHintResolver $resolver
    ) {
        if ($this->hint) {
            $hint = $resolver->resolveTypeHint($store, $this->hint);
            if($hint) {
                $this->hint($hint);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isStatic()
    {
        return $this->getOrSet('isStatic', ...func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return $this->getOrSet('name', ...func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public static function readFromXMI(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleBuilder $builder
    ) {
        $element = new static();
        $element->xmiId($reader->xmiId());
        $element->name($reader->shortName());
        $element->hint($reader->type());
        $element->comment($reader->comment());
        $element->visibility($reader->visibility());
        $element->isStatic($reader->isStatic());
        return $element;
    }

    /**
     * {@inheritDoc}
     */
    public function visibility()
    {
        return $this->getOrSet('visibility', ...func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function xmiId()
    {
        return $this->getOrSet('xmiId', ...func_get_args());
    }
}
