<?php
namespace XMITools;

/**
 * Class class CodeFormatter
 * Common logic for formatting source code.
 */
class CodeFormatter
{
    const TAB = '    ';
    const MAX_LINE_LENGTH = 80;

    /**
     * Adjust length of lines to be less than 80 chars if possible.
     * @param int $indent Indentation level (number of TAB prefixes).
     * @param array $lines
     * @return array
     */
    public static function adjustCommentLines($indent, $lines)
    {
        /*
         * make sure lines have comment prefix
         * assume first/last lines have begin/end comment characters
         */
        $cmp = static::prefixComment('', $indent);
        $n = count($lines) - 1;
        for ($i = 1; $i < $n; $i++) {
            if (0 !== strpos($lines[$i], $cmp)) {
                $lines[$i] = static::prefixComment($lines[$i], $indent);
            }
        }
        /* apply split algorithm to comment lines */
        $result = [];
        foreach ($lines as $line) {
            array_splice(
                $result,
                count($result),
                0,
                call_user_func(
                    [get_called_class(), 'splitCommentLine'],
                    $line,
                    $indent
                )
            );
        }
        return $result;
    }

    /**
     * Gets source code string representation of a PHP array.
     * @param array $in
     * @return string
     */
    public static function arrayToSourceCodeString(array $in)
    {
        $tmp = [];
        $lead = 0;
        foreach (explode("\n", var_export($in, true)) as $line) {
            $indent = strspn($line, ' ') / 2;
            $line = ltrim($line);
            switch ($line) {
                case '),':
                    $tmp[] = sprintf(
                        '%s],',
                        str_repeat(self::TAB, $indent + $lead)
                    );
                    break;
                case ')':
                    $tmp[] = sprintf(
                        '%s]',
                        str_repeat(self::TAB, $indent + $lead)
                    );
                    break;
                case 'array (':
                    $line = '[';
                    if (count($tmp)) {
                        $line = sprintf('%s[', ltrim(array_pop($tmp)));
                    }
                default:
                    $tmp[] = sprintf(
                        '%s%s',
                        str_repeat(self::TAB, $indent + $lead),
                        $line
                    );
                    break;
            }
            $lead = 1;
        }
        return implode("\n", $tmp);
    }

    /**
     * Create a bitmap for a set of bool values.
     * @param bool $args Multiple bool arguments.
     * @return int
     *
     */
    public static function mapFlags()
    {
        $flags = 0;
        $mask = 1;
        foreach (func_get_args() as $arg) {
            if ($arg) {
                $flags |= $mask;
            }
            $mask <<= 1;
        }
        return $flags;
    }

    /**
     * Adds prefix to a comment line.
     * @param string $comment Comment line to prefix.
     * @return string Comment line with prefix.
     */
    public static function prefixComment($comment, $indent = 1)
    {
        return sprintf('%s * %s',
            str_repeat(self::TAB, $indent),
            $comment
        );
    }

    /**
     * Splits a comment in to brief and annotations.
     * Prevents output of @doctrine annotated sections.
     */
    public static function splitComment(
        $comment,
        array &$brief,
        array &$annotations
    ) {
        $brief = [ "@todo documentation" ];
        $tmp = [];
        if (0 < strlen($comment)) {
            $tmp = explode("\n", $comment);
            $brief = [];
        }
        /* insert parameters before other annotations */
        while (
            count($tmp)
            && ord('@') != ord(reset($tmp))
        ) {
            $brief[] = array_shift($tmp);
        }
        $keep = true;
        while (count($tmp)) {
            $str = array_shift($tmp);
            if (0 === strpos($str, '@doctrine')) {
                $keep ^= true;
            } elseif ($keep) {
                $annotations[] = $str;
            }
        }
    }

    /**
     * Splits a comment line on spaces such that
     * each line is < MAX_LINE_LENGTH characters long.
     * @param string $line Comment line to split.
     * @return array Split lines.
     * @pre assumes line already has comment prefix.
     */
    public static function splitCommentLine($line, $indent = 1)
    {
        $result = [];
        $start = $indent * strlen(self::TAB) + 3;
        $end = self::MAX_LINE_LENGTH;
        $singleSplit = false;
        while (self::MAX_LINE_LENGTH <= strlen($line)) {
            /* quick check for single line docblock ending */
            $pos = strpos($line, ' */');
            if (false !== $pos) {
                $singleSplit = true;
                $line = substr($line, $pos);
            }
            /*
             * start with first space after $start and before $end
             * then find last space before $end
             * $offset defaults to full length to prevent infinite loop
             * in the case no spaces are between $start and $end.
             */
            $offset = strlen($line);
            $pos = strpos($line, ' ', $start);
            while (false !== $pos && $pos < $end) {
                $offset = $pos;
                $pos = strpos($line, ' ', $pos + 1);
            }
            /*
             * put isolated part in results
             * then setup remaining part for same loop
             */
            $result[] = substr($line, 0, $offset);
            /* skip space $offset points to */
            $line = substr($line, $offset + 1);
            if (0 < strlen($line)) {
                $line = static::prefixComment($line, $indent);
            }
        }
        if (0 < strlen($line)) {
            $result[] = $line;
        }
        if ($singleSplit) {
            $result[] = sprintf('%s */', str_repeat(self::TAB, $indent));
        }
        return $result;
    }
}
