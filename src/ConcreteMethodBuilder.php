<?php
namespace XMITools;

/**
 * Class ConcreteMethodBuilder
 * Builder for concrete methods.
 */
class ConcreteMethodBuilder extends InterfaceMethodBuilder
{
    /**
     * Indents lines of code.
     */
    protected function indentCode($line)
    {
        if (0 < strlen($line)) {
            return str_repeat(self::TAB, 2) . $line;
        }
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected function prototype($indent = 1)
    {
        $sig = parent::prototype($indent);
        $last = array_pop($sig);
        $last = rtrim($last, ';');
        if (false === strpos($last, '(')) {
            $last .= ' {';
            $sig[] = $last;
        } else {
            $sig[] = $last;
            $sig[] = self::TAB . '{';
        }
        $code = [];
        if (0 < strlen($this->code)) {
            $code = array_map(
                [$this, 'indentCode'],
                explode("\n", $this->code)
            );
        }
        return array_merge(
            $sig,
            $code,
            [ self::TAB . '}' ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function sourceCode()
    {
        return array_merge(
            $this->docblock(),
            $this->prototype()
        );
    }
}
