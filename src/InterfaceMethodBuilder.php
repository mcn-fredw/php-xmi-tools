<?php
namespace XMITools;

use ReflectionMethod;

/**
 * Class InterfaceMethodBuilder
 * Builder for interface methods.
 */
class InterfaceMethodBuilder extends MethodBuilder implements
    Interfaces\ReflectionMethodReader
{
    /** Reflected doc block and signature. */
    protected $reflected = [];

    /**
     * Creates source code lines for method doc block.
     * @return array Lines to not have terminating new-line characters.
     */
    protected function docblock($indent = 1)
    {

        $brief = [];
        $annotations = [];
        CodeFormatter::splitComment($this->comment(), $brief, $annotations);
        return CodeFormatter::adjustCommentLines(
            $indent,
            array_merge(
                [str_repeat(self::TAB, $indent) . '/**'],
                $brief,
                $this->parametersDocBlock($indent),
                $annotations,
                [str_repeat(self::TAB, $indent) . ' */']
            )
        );
    }

    /**
     * Gets doc block section for method parameters.
     * @return array
     */
    protected function hintPrototype()
    {
        $hint = $this->hint();
        if (0 === strpos($hint, '@')) {
            $hint = substr($hint, 1);
        }
        if (0 == strlen($hint)) {
            /* no hint */
            return ');';
        }
        if ('mixed' == $hint || 2 == count(explode('|', $hint))) {
            /* mixed type can't be hinted */
            return ');';
        }
        return sprintf('): %s;', $hint);
    }

    /**
     * Gets doc block section for method parameters.
     * @return array
     */
    protected function parametersDocBlock($indent)
    {
        $result = [];
        foreach ($this->parameters as $param) {
            foreach (explode("\n", $param->docblock()) as $line) {
                $result[] = $line;
            }
        }
        if ($this->hint) {
            $hint = $this->hint();
            if (0 === strpos($hint, '@')) {
                $hint = substr($hint, 1);
            }
            $result[] = '@return ' . $hint;
        }
        return $result;
    }

    /**
     * Gets prototype of method parameters in multi line format.
     * @return array
     */
    protected function multiLineParameterPrototype($indent = 2)
    {
        $n = count($this->parameters);
        $proto = [];
        foreach ($this->parameters as $param) {
            $n -= 1;
            $frmt = '%s%s';
            if (0 < $n) {
                $frmt = '%s%s,';
            }
            $proto[] = sprintf(
                $frmt,
                str_repeat(self::TAB, $indent),
                $param->prototype()
            );
        }
        return $proto;
    }

    /**
     * Gets prototype of method parameters in single line format.
     * @return string
     */
    protected function singleLineParameterPrototype()
    {
        $sep = '';
        $proto = '';
        foreach ($this->parameters as $param) {
            $proto .= $sep . $param->prototype();
            $sep = ', ';
        }
        return $proto;
    }

    /**
     * Creates method signature.
     * @return array
     */
    protected function prototype($indent = 1)
    {
        $fn = str_repeat(self::TAB, $indent);
        if ($this->isAbstract) {
            $fn .= 'abstract ';
        }
        $fn .= $this->visibility;
        if ($this->isStatic) {
            $fn .= ' static';
        }
        $hint = $this->hintPrototype();
        $fn .= ' function ' . $this->name . '(';
        $params = $this->singleLineParameterPrototype();
        $len = strlen($fn) +strlen($params) + strlen($hint);
        if (self::MAX_LINE_LENGTH > $len) {
            return [ $fn . $params . $hint ];
        }
        return array_merge(
            [ $fn ],
            $this->multiLineParameterPrototype($indent + 1),
            [ str_repeat(self::TAB, $indent) . $hint ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function readFromReflectionMethod(
        ReflectionMethod $reflection,
        Interfaces\ReflectionTypeHintResolver $resolver,
        Interfaces\ModuleStore $store,
        array &$lines
    ) {
        /* grab doc block */
        if ($doc = $reflection->getDocComment()) {
            $this->reflected = explode("\n", self::TAB . $doc);
        }
        /* grab signature and source code */
        $start = $reflection->getStartLine() - 1;
        $end = $reflection->getEndLine() - 1;
        while ($start <= $end) {
            if (isset($lines[$start])) {
                $this->reflected[] = $lines[$start];
            } else {
                echo "method {$this->name} line $start not found\n";
            }
            $start++;
        }
        /* tell resolver about name space imports */
        if ($reflection->hasReturnType()) {
            $resolver->resolveReflectionType(
                $store,
                $reflection->getReturnType()
            );
        }
        if (0 < $reflection->getNumberOfParameters()) {
            foreach ($reflection->getParameters()  as $param) {
                $resolver->resolveReflectionType(
                    $store,
                    $param->getType()
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sourceCode()
    {
        if (0 < count($this->reflected)) {
            return $this->reflected;
        }
        return array_merge(
            $this->docblock(),
            $this->prototype()
        );
    }
}
