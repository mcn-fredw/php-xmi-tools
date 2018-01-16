<?php
namespace XMITools\Traits;

/**
 * Trait FormatHint
 * Parameter and attribute helper to format type hints.
 */
trait FormatHint
{
    /**
     * Gets formatted hint.
     * @param bool $isProto True if formatting for prototype,
     * false if formatting for doc block.
     * @return string
     */
    protected function formatHint($isProto)
    {
        $hint = $this->hint;
        if (0 === strpos($hint, '@')) {
            $hint = substr($hint, 1);
        }
        if (! $isProto) {
            if (0 === strpos($hint, '?')) {
                $hint = substr($hint, 1) . '|null';
            }
            if (0 < strlen($hint) && 'null' == $this->value) {
                $hint = $hint . '|null';
            }
        }
        if ($isProto && false !== strpos($hint, '|')) {
            $hint = '';
        }
        return $hint;
    }
}
