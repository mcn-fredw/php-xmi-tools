<?php
namespace XMITools;

/**
 * Class ParameterBuilder
 * Builder for method parameters.
 */
class ParameterBuilder implements Interfaces\ParameterBuilder
{
    use Traits\FormatHint;
    use Traits\FormatName;

    protected $comment;
    protected $hint;
    protected $hintXmi;
    protected $name;
    protected $typeResolved = false;
    protected $value;
    protected $xmiId;
    protected static $docBlockFormats = [
        '@param %2$s',
        '@param %1$s %2$s',
        '@param %2$s %3$s',
        '@param %1$s %2$s %3$s'
    ];
    protected static $prototypeFormats = [
        '%2$s',
        '%1$s %2$s',
        '%2$s = %3$s',
        '%1$s %2$s = %3$s'
    ];

    /**
     * {@inheritDoc}
     */
    public function copy()
    {
        $dst = new static();
        $dst->xmiId = $this->xmiId;
        $dst->name = $this->name;
        $dst->hint = $this->hint;
        $dst->hintXmi = $this->hintXmi;
        $dst->comment = $this->comment;
        $dst->value = $this->value;
        return $dst;
    }

    /**
     * {@inheritDoc}
     */
    public function docBlock()
    {
        return sprintf(
            self::$docBlockFormats[$this->formatSelector(false)],
            $this->formatHint(false),
            $this->formatName(false),
            $this->comment
        );
    }

    /**
     * Gets format selector.
     * @param bool $isProto True if wanting prototype selector.
     * @return int
     * 0 = just name
     * 1 = name and hint
     * 2 = name and (comment or value)
     * 3 = name, hint and (comment or value)
     */
    protected function formatSelector($isProto)
    {
        $val = $this->comment;
        if ($isProto) {
            $val = $this->value;
        }
        $format = 0;
        if (0 < strlen($this->formatHint($isProto))) {
            $format = 1;
        }
        if (0 < strlen($val)) {
            $format |= 2;
        }
        return $format;
    }

    /**
     * {@inheritDoc}
     */
    public function hint()
    {
        return $this->hint;
    }

    /**
     * {@inheritDoc}
     */
    public function hintXmi()
    {
        return $this->hintXmi;
    }

    /**
     * {@inheritDoc}
     */
    public function importTypes(
        Interfaces\ModuleStore $store,
        Interfaces\TypeHintResolver $resolver
    ) {
        if ($this->typeResolved) {
            return;
        }
        $this->typeResolved = true;
        if ($this->hintXmi) {
            $hint = $resolver->resolveTypeHint($store, $this->hintXmi);
            if($hint) {
                $this->hint = $hint;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function prototype()
    {
        return sprintf(
            self::$prototypeFormats[$this->formatSelector(true)],
            $this->formatHint(true),
            $this->formatName(true),
            $this->value
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function readFromXMI(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleBuilder $builder
    ) {
        $parameter = new static();
        $parameter->xmiId = $reader->xmiId();
        $parameter->name = $reader->shortName();
        $parameter->hint = $reader->type();
        $parameter->hintXmi = $parameter->hint;
        $parameter->comment = $reader->comment();
        $parameter->value = $reader->value();
        return $parameter;
    }
}
