<?php
namespace XMITools\Traits;

/**
 * Trait FormatName
 * Parameter and attribute helper to format variable names.
 */
trait FormatName
{
    /**
     * Gets formatted parameter name.
     * @param bool $isProto True if formatting for prototype,
     * false if formatting for doc block.
     * @return string
     */
    protected function formatName($isProto)
    {
        $name = $this->name;
        if (0 < strlen($name) && false === strpos($name, '$')) {
            $name = '$' . $name;
        }
        return $name;
    }
}
