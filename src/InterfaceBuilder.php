<?php
namespace XMITools;

use ReflectionMethod;
use ReflectionType;
use UnexpectedValueException;

/**
 * Builder for interfaces.
 */
class InterfaceBuilder extends BaseModuleBuilder implements
    Interfaces\AbstractionConsumer,
    Interfaces\TypeImporter,
    Interfaces\TypeHintResolver,
    Interfaces\ModuleWriter,
    Interfaces\SourceCodeMethodImporter,
    Interfaces\ReflectionTypeHintResolver
{
    const MAX_LINE_LENGTH = CodeFormatter::MAX_LINE_LENGTH;
    const TAB = CodeFormatter::TAB;
    const TYPE_NAME = 'interface';
    const METHOD_BUILER_NAME = 'interface-method-builder';
    const METHOD_REFLECTION_READER = 'interface-reflection-method-reader';

    protected $comment = '';
    protected $extends = [];
    protected $imports = [];
    protected $methods = [];

    /**
     * {@inheritDoc}
     */
    public function bindAbstraction(
        Interfaces\Module $supplier
    ) {
        $id = $supplier->xmiId();
        $import = $supplier->importFor($this->ns);
        $hint = $supplier->typeHintFor($this->ns);
        $this->bindAbstractionData(
            true,
            $this->extends,
            $id,
            $import,
            $hint
        );
    }

    /**
     * Saves abstraction data.
     * @param bool $test Flags save the import and hint.
     * @param array &hints Where to store the hint.
     * @param string $id XMI id for the supplier module.
     * @param string $import Target of a use statement to import
     * supplier module in to this namespace.
     * @param string $hint Type hint to use for the abstraction.
     */
    protected function bindAbstractionData(
        $test,
        &$hints,
        $id,
        $import,
        $hint
    ) {
        if ($test && $hint) {
            $hints[$id] = $hint;
            if ($import) {
                $this->imports[$id] = $import;
            }
        }
    }

    /**
     * Callback for saving a method.
     * @param Interfaces\XMIReader $reader Has parameter node.
     * @param Interfaces\Module $builder Pass through is this.
     */
    public function buildMethodFromXMI(
        Interfaces\XMIReader $reader,
        Interfaces\Module $builder
    ) {
        $methodFactory = FactoryService::get(static::METHOD_BUILER_NAME);
        $method = call_user_func($methodFactory, $reader, $builder);
        $this->methods[$method->name()] = $method;
    }

    /**
     * {@inheritDoc}
     */
    public function gatherElements(
        Interfaces\XMIReader $reader
    ) {
        $reader->walkMethods([$this, 'buildMethodFromXMI'], $this);
    }

    /**
     * {@inheritDoc}
     */
    public function isInterface()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function importTypes(
        Interfaces\ModuleStore $store,
        Interfaces\TypeHintResolver $resolver
    ) {
        foreach (array_keys($this->methods) as $index) {
            $this->methods[$index]->importTypes($store, $resolver);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function importSourceCodeMethod(
        ReflectionMethod $reflection,
        Interfaces\ModuleStore $store,
        array &$lines
    ) {
        $name = $reflection->getName();
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();
        if (
            false !== $start
            && isset($lines[$start - 1])
            && isset($lines[$end - 1])
        ) {
            /* method is in this module */
            $method = null;
            if (isset($this->methods[$name])) {
                $method = $this->methods[$name];
            } else {
                $methodClass = FactoryService::get(
                    static::METHOD_REFLECTION_READER
                );
                $method = new $methodClass();
                $method->name($name);
            }
            $method->readFromReflectionMethod(
                $reflection,
                $this,
                $store,
                $lines
            );
            $this->methods[$name] = $method;
        }
    }

    /**
     * Builds attributes section of module source code.
     * @return array
     */
    protected function moduleAttributes()
    {
        return [];
    }

    /**
     * Builds constants section of module source code.
     * @return array
     */
    protected function moduleConstants()
    {
        return [];
    }

    /**
     * Builds module doc block section of module source code.
     * @return array
     */
    protected function moduleDocblock()
    {
        return CodeFormatter::adjustCommentLines(
            0,
            array_merge(
                [ '/**' ],
                explode("\n", $this->comment),
                [ ' */' ]
            )
        );
    }

    /**
     * Builds use imports section of module source code.
     * @return array
     */
    protected function moduleImports()
    {
        $lines = [];
        foreach ($this->imports as $import) {
            $lines[] = "use $import;";
        }
        if (count($lines)) {
            $lines[] = '';
        }
        return $lines;
    }

    /**
     * Builds methods section of module source code.
     * @return array
     */
    protected function moduleMethods()
    {
        $lines = [];
        $blank = false;
        foreach ($this->methods as $method) {
            if ($blank) {
                $lines[] = '';
            }
            array_splice(
                $lines,
                count($lines),
                0,
                $method->sourceCode()
            );
            $blank = true;
        }
        return $lines;
    }

    /**
     * Builds module's declaration section of module source code.
     * @return array
     */
    protected function moduleDeclaration()
    {
        $proto = $this->moduleTypeAndName();
        $lines = (array)$proto;
        $tab = CodeFormatter::TAB;
        if (0 < count($this->extends)) {
            $proto .= " extends";
            $lines = (array)$proto;
            $sep = ' ';
            $last = end($this->extends);
            foreach ($this->extends as $hint) {
                /* single line format */
                $proto .= "{$sep}{$hint}";
                $sep = ', ';
                /* multi line format */
                if ($last == $hint) {
                    $lines[] = "{$tab}{$hint}";
                } else {
                    $lines[] = "{$tab}{$hint},";
                }
            }
            if (self::MAX_LINE_LENGTH > strlen($proto)) {
                /* use single line format */
                $lines = (array)$proto;
            }
        }
        return $lines;
    }

    /**
     * Builds module's source code.
     * @return array
     */
    protected function moduleSourceCode()
    {
        return array_merge(
            [
                '<?php',
                "namespace {$this->ns};",
                ''
            ],
            $this->moduleImports(),
            $this->moduleStubs(),
            $this->moduleDocblock(),
            $this->moduleDeclaration(),
            [
                '{'
            ],
            $this->moduleTraits(),
            $this->moduleConstants(),
            $this->moduleAttributes(),
            $this->moduleMethods(),
            [
                '}',
            ]
        );
    }

    /**
     * Builds module's stub classes and traits section of module source code.
     * @return array
     */
    protected function moduleStubs()
    {
        return [];
    }

    /**
     * Builds module's use traits section of module source code.
     * @return array
     */
    protected function moduleTraits()
    {
        return [];
    }

    /**
     * Gets module type and name for source code output.
     * @return string
     */
    protected function moduleTypeAndName()
    {
        return static::TYPE_NAME . " {$this->name}";
    }

    /**
     * {@inheritDoc}
     */
    public function resolveReflectionType(
        Interfaces\ModuleStore $store,
        ReflectionType $reflection = null
    ) {
        if (is_null($reflection) || $reflection->isBuiltin()) {
            return;
        }
        $name = $store->importFor($this->ns, (string)$reflection);
        foreach ($this->imports as $import) {
            if ($import == $name) {
                /* already know this import */
                return;
            }
        }
        $this->imports[$name] = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveTypeHint(
        Interfaces\ModuleStore $store,
        $typeXmiId
    ) {
        $hint = '';
        $module = $store->getModule($typeXmiId);
        if ($module) {
            $import = $module->importFor($this->ns);
            $hint = $module->typeHintFor($this->ns);
            if ($hint && $import) {
                $this->imports[$typeXmiId] = $import;
            }
        }
        return $hint;
    }

    /**
     * {@inheritDoc}
     */
    public function writeModule(
        Interfaces\ModuleStore $store,
        Interfaces\PathTranslator $paths
    ) {
        $path = $paths->pathForName($this->fullName());
        echo "Writing source code $path\n";
        $lines = $this->moduleSourceCode();
        $fd = fopen($path, 'w');
        foreach ($lines as $line) {
            if (is_array($line)) {
                var_dump($line);
                throw new UnexpectedValueException("writing file $path");
            }
            fwrite($fd, "$line\n");
        }
        fclose($fd);

        return;
    }
}
