<?php
namespace XMITools;

use ReflectionProperty;
use ReflectionClassConstant;
use UnexpectedValueException;
use Error;

/**
 * Class AttributeBuilder
 * Builder for a class attribute.
 */
class AttributeBuilder extends ClassElementBuilder implements
    Interfaces\AttributeBuilder,
    Interfaces\RefelectionConstantReader,
    Interfaces\ReflectionPropertyReader
{
    use Traits\FormatHint;
    use Traits\FormatName;

    protected static $propertyDeclarationFormats = [
        '%1$s%2$s %4$s;',
        '%1$s%2$s %3$s %4$s;',
        '%1$s%2$s %4$s = %5$s;',
        '%1$s%2$s %3$s %4$s = %5$s;'
    ];
    protected static $propertDcocblockFormats = [
        '%1$s/** @todo documentation */',
        '%1$s/** @var %2$s */',
        '%1$s/** %3$s */',
        '%1$s/** @var %2$s %3$s */'
    ];
    protected static $constantDcocblockFormats = [
        '%1$s/** @todo documentation */',
        '%1$s/** %2$s */'
    ];
    /** Attribute value. */
    protected $value;
    /** Reflected doc block and declaration. */
    protected $reflected = [];

    /**
     * Generate declaration for a constant.
     * @return array
     */
    protected function constantDeclaration()
    {
        return [
            sprintf(
                '%sconst %s = %s;',
                str_repeat(self::TAB, 1),
                $this->name,
                $this->value
            )
        ];
    }

    /**
     * Generate doc block for a constant.
     * @return array
     */
    protected function constantDocblock()
    {
        $doc = sprintf(
            static::$constantDcocblockFormats[
                (0 < strlen($this->comment))
            ],
            str_repeat(self::TAB, 1),
            $this->comment
        );
        if (self::MAX_LINE_LENGTH > strlen($doc)) {
            return (array)$doc;
        }
        return CodeFormatter::adjustCommentLines(
            1,
            [
                self::TAB . '/**',
                $this->comment,
                self::TAB . ' */'
            ]
        );
    }

    /**
     * Generate source code for a constant.
     * @return array
     */
    protected function constantSourceCode()
    {
        if (0 < count($this->reflected)) {
            return $this->reflected;
        }
        return array_merge(
            $this->constantDocblock(),
            $this->constantDeclaration()
        );
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
                $this->hint = $hint;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isAnnotation()
    {
        return (
            'implementation' == $this->visibility
            && '@annotation' == $this->hint
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isConst()
    {
        return (
            'implementation' == $this->visibility
            && '@const' == $this->hint
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isTest()
    {
        return (
            'implementation' == $this->visibility
            && '@test' == $this->hint
        );
    }

    /**
     * Generate declaration for a property.
     * @return array
     */
    protected function propertyDeclaration()
    {
        return [
            sprintf(
                static::$propertyDeclarationFormats[
                    CodeFormatter::mapFlags(
                        $this->isStatic(),
                        (0 < strlen($this->value))
                    )
                ],
                str_repeat(self::TAB, 1),
                $this->visibility,
                'static',
                $this->formatName(true),
                $this->value
            )
        ];
    }

    /**
     * Generate doc block for a property.
     * @return array
     */
    protected function propertyDocblock()
    {
        $selector = CodeFormatter::mapFlags(
            (0 < strlen($this->hint)),
            (0 < strlen($this->comment))
        );
        $brief = [];
        $annotations = [];
        CodeFormatter::splitComment($this->comment(), $brief, $annotations);
        if (
            1 == count($brief)
            && 0 == count($annotations)
            && self::MAX_LINE_LENGTH > (strlen(reset($brief)) + 11)
        ) {
            return (array)sprintf(
                static::$propertDcocblockFormats[$selector],
                str_repeat(self::TAB, 1),
                $this->formatHint(false),
                reset($brief)
            );
        }
        return CodeFormatter::adjustCommentLines(
            1,
            array_merge(
                [self::TAB . '/**'],
                $brief,
                $annotations,
                ['@var ' . $this->formatHint(false)],
                [self::TAB . ' */']
            )
        );
    }

    /**
     * Generate source code for class property.
     * @return array
     */
    protected function propertySourceCode()
    {
        if (0 < count($this->reflected)) {
            return $this->reflected;
        }
        return array_merge(
            $this->propertyDocblock(),
            $this->propertyDeclaration()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function readFromReflectionConstant(
        ReflectionClassConstant $reflection,
        Interfaces\ReflectionTypeHintResolver $resolver,
        Interfaces\ModuleStore $store
    ) {
        $this->name($reflection->getName());
        $this->visibility('implementation');
        $this->hint('@const');
        $value = '';
        try {
            $value = $reflection->getValue();
        } catch (Error $er) {
            throw new UnexpectedValueException('weird error');
        }
        switch (true) {
            case is_array($value);
                $this->value(
                    CodeFormatter::arrayToSourceCodeString($value)
                );
                break;
            default;
                $this->value(var_export($value, true));
                break;
        }
        $this->reflected = array_merge(
            (array)(self::TAB . $reflection->getDocComment()),
            $this->constantDeclaration()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function readFromReflectionProperty(
        ReflectionProperty $reflection,
        array &$defaults,
        Interfaces\ReflectionTypeHintResolver $resolver,
        Interfaces\ModuleStore $store,
        array &$lines
    ) {
        $this->isStatic($reflection->isStatic());
        $this->name($reflection->getName());
        if (isset($defaults[$this->name()]))  {
            $value = $defaults[$this->name()];
            switch (true) {
                case is_array($value);
                    $this->value(
                        CodeFormatter::arrayToSourceCodeString($value)
                    );
                    break;
                default;
                    $this->value(var_export($value, true));
                    break;
            }
        }
        if ($reflection->isPublic()) {
            $this->visibility('public');
        }
        if ($reflection->isProtected()) {
            $this->visibility('protected');
        }
        if ($reflection->isPrivate()) {
            $this->visibility('private');
        }
        $this->reflected = array_merge(
            (array)(self::TAB . $reflection->getDocComment()),
            $this->propertyDeclaration()
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function readFromXMI(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleBuilder $builder
    ) {
        $attribute = parent::readFromXMI($reader, $builder);
        $attribute->value = $reader->initialValue();
        return $attribute;
    }

    /**
     * {@inheritDoc}
     */
    public function sourceCode()
    {
        if ($this->isConst()) {
            return $this->constantSourceCode();
        }
        if ($this->isAnnotation()) {
            return [];
        }
        if ($this->isTest()) {
            return [];
        }
        return $this->propertySourceCode();
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->getOrSet('value', ...func_get_args());
    }
}
