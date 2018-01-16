<?php
namespace XMITools;

use ReflectionMethod;

/**
 * Class ExternalConcreteMethodBuilder
 * Builder to import method from source file.
 */
class ExternalConcreteMethodBuilder extends BaseMethodBuilder
{
    const TESTING = true;
    /**
     * Imports existing method body.
     * @param string $path Full module path.
     * @param string $methodName Name of method to import.
     * @pre Module has already been passed to require_once().
     * @post $this->method[$methodName]['body'] has method body.
     * Body is in compressed format (\t = '    ').
     */
    protected function importMethodBody($path, $methodName)
    {
        $mr = new ReflectionMethod(
            $this->fullName() . '::' . $methodName
        );
        $start = $mr->getStartLine() - 1;
        $end = $mr->getEndLine();
        $this->methods[$methodName] = array_merge(
            $this->compressLines(
                explode("\n", "    " . $mr->getDocComment())
            ),
            $this->compressLines(
                array_slice(file($path), $start, $end - $start)
            )
        );
    }

}
