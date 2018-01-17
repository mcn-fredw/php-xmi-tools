<?php
namespace XMITools;

use ReflectionClass;
use ReflectionProperty;
use ReflectionClassConstant;

/**
 * Class ClassBuilder
 * Base builder for PHP classes.
 */
class ClassBuilder extends InterfaceBuilder implements
    Interfaces\MethodImporter,
    Interfaces\SourceCodeAttributeImporter,
    Interfaces\SourceCodeConstantImporter,
    Interfaces\SourceCodeTraitImporter
{
    const TYPE_NAME = 'class';
    const METHOD_BUILER_NAME = 'class-method-builder';

    protected $annotations = [];
    protected $attributes = [];
    protected $constants = [];
    protected $implements = [];
    protected $templates = [];
    protected $tests = [];
    protected $traits = [];

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
            $supplier->isInterface(),
            $this->implements,
            $id,
            $import,
            $hint
        );
        $this->bindAbstractionData(
            $supplier->isClass(),
            $this->extends,
            $id,
            $import,
            $hint
        );
        $this->bindAbstractionData(
            $supplier->isTrait(),
            $this->traits,
            $id,
            $import,
            $hint
        );
    }

    /**
     * Callback for building a class attribute.
     */
    public function buildAttribute(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleBuilder $builder
    ) {
        $attributeFactory = FactoryService::get('attribute-builder');
        $attribute = call_user_func($attributeFactory, $reader, $builder);
        /*
         * store in annotations for now,
         * figure out correct storage array in importTypes()
         */
        $this->annotations[$attribute->name()] = $attribute;
    }

    /**
     * Callback for building a template parameter.
     */
    public function buildTemplateParameter(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleBuilder $builder
    ) {
        $parameterFactory = FactoryService::get('template-parameter-builder');
        $parameter = call_user_func($parameterFactory, $reader, $builder);
        $this->templates[$parameter->name()] = $parameter;
    }

    /**
     * Calculate import and hint for a reflected source code import.
     * @param string $fullName
     * @param string &$import
     * @param string &$hint
     */
    protected function calcImportAndHint($fullName, &$import, &$hint)
    {
        $pos = strpos($fullName, $this->ns);
        if (0 === $pos) {
            /* full name is in sub name space */
            $import = '';
            $hint = substr($fullName, strlen($this->ns) + 1);
            return;
        }
        /* full name is not in sub name space */
        $pos = strrpos($fullName, '\\');
        $hint = substr($fullName, $pos + 1);
        $import = substr($fullName, 0, $pos);
    }

    /**
     * {@inheritDoc}
     */
    public function gatherElements(
        Interfaces\XMIReader $reader
    ) {
        $reader->walkMethods([$this, 'buildMethodFromXMI'], $this);
        $reader->walkAttributes([$this, 'buildAttribute'], $this);
        $reader->walkTemplates([$this, 'buildTemplateParameter'], $this);
    }

    /**
     * Tests class has already knows method.
     * @param string $methodKey
     * @param Interfaces\ModuleStore $store
     * @return bool
     */
    public function hasMethod(
        $methodKey,
        Interfaces\ModuleStore $store
    ) {
        if (isset($this->methods[$methodKey])) {
            return true;
        }
        foreach (array_keys($this->traits) as $trait) {
            $module = $store->getModule($trait);
            if ($module->hasMethod($methodKey, $store)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Import methods from an interface.
     * @param string $interfaceKey
     * @param Interfaces\ModuleStore $store
     */
    public function importAbstractionMethods(
        $interfaceKey,
        Interfaces\ModuleStore $store
    ) {
        $module = $store->getModule($interfaceKey);
        foreach ($module->methods() as $methodKey => $method) {
            if ($this->hasMethod($methodKey, $store)) {
                /* method impl already declared */
                continue;
            }
            /* copy method from interface */
            $sourceClass = FactoryService::get('class-method-class');
            $this->methods[$methodKey] = new $sourceClass();
            $this->methods[$methodKey]->copy($method);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function importMethods(
        Interfaces\ModuleStore $store
    ) {
        foreach (array_keys($this->implements) as $ifaceKey) {
            $this->importAbstractionMethods($ifaceKey, $store);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function importSourceCodeAttribute(
        ReflectionProperty $reflection,
        array &$defaults,
        Interfaces\ModuleStore $store,
        array &$lines
    ) {
        $decl = $reflection->getDeclaringClass()->getName();
        if ($decl != $this->fullName()) {
            return;
        }
        $name = $reflection->getName();
        if (! isset($this->attributes[$name])) {
            $class = FactoryService::get('attribute-class');
            $this->attributes[$name] = new $class();
            $this->attributes[$name]->name($name);
        }
        $this->attributes[$name]->readFromReflectionProperty(
            $reflection,
            $defaults,
            $this,
            $store,
            $lines
        );
    }

    /**
     * {@inheritDoc}
     */
    public function importSourceCodeConstant(
        ReflectionClassConstant $reflection,
        Interfaces\ModuleStore $store
    ) {
        $decl = $reflection->getDeclaringClass()->getName();
        if ($decl != $this->fullName()) {
            return;
        }
        $name = $reflection->getName();
        if (! isset($this->constants[$name])) {
            $class = FactoryService::get('constant-class');
            $this->constants[$name] = new $class();
            $this->constants[$name]->name($name);
        }
        $this->constants[$name]->readFromReflectionConstant(
            $reflection,
            $this,
            $store
        );
    }

    /**
     * {@inheritDoc}
     * @todo if parent class uses trait, filter out of sub class.
     */
    public function importSourceCodeTrait(
        ReflectionClass $reflection,
        Interfaces\ModuleStore $store,
        array &$lines
    ) {
        /* remove trait attributes from using class/trait */
        foreach ($reflection->getProperties() as $prop) {
            $propName = $prop->getName();
            unset($this->attributes[$propName]);
        }
        $import = '';
        $hint = '';
        $this->calcImportAndHint($reflection->getName(), $import, $hint);
        if (in_array($hint, $this->traits)) {
            /* already know trait */
            return;
        }
        /* add trait to class */
        if (0 < strlen($import)) {
            /* need import for trait */
            $this->imports[$hint] = $import;
        }
        $this->traits[$hint] = $hint;
    }

    /**
     * {@inheritDoc}
     */
    public function isClass()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isInterface()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function importTypes(
        Interfaces\ModuleStore $store,
        Interfaces\TypeHintResolver $resolver
    ) {
        /* interface has logic for methods */
        parent::importTypes($store, $resolver, 'class-method-builder');

        foreach ($this->templates as $template) {
            $template->importTypes($store, $resolver);
        }

        $attributes = $this->annotations;
        $this->annotations = [];
        foreach ($attributes as $an => $attribute) {
            $attribute->importTypes($store, $resolver);
            if ($attribute->isConst()) {
                $this->constants[$an] = $attribute;
            } elseif ($attribute->isTest()) {
                $this->tests[$an] = $attribute;
            } elseif ($attribute->isAnnotation()) {
                $this->annotations[$an] = $attribute;
            } else {
                $this->attributes[$an] = $attribute;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function moduleAttributes()
    {
        $lines = [];
        foreach ($this->attributes as $attribute) {
            $line = $attribute->sourceCode();
            if (0 < count($line)) {
                array_splice($lines, count($lines), 0, $line);
            }
        }
        if (0 < count($lines)) {
            $lines[] = '';
        }
        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    protected function moduleConstants()
    {
        $lines = [];
        foreach ($this->constants as $constant) {
            $line = $constant->sourceCode();
            if (0 < count($line)) {
                array_splice($lines, count($lines), 0, $line);
            }
        }
        if (0 < count($lines)) {
            $lines[] = '';
        }
        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    protected function moduleDeclaration()
    {
        $proto = $this->moduleTypeAndName();
        $tab = CodeFormatter::TAB;
        if (0 < count($this->extends)) {
            $proto .= " extends " . reset($this->extends);
        }
        $lines = (array)$proto;
        if (0 < count($this->implements)) {
            $proto .= " implements";
            $lines = (array)$proto;
            $sep = ' ';
            $last = end($this->implements);
            foreach ($this->implements as $hint) {
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
     * {@inheritDoc}
     */
    protected function moduleTraits()
    {
        $lines = [];
        $tab = self::TAB;
        foreach ($this->traits as $hint) {
            $lines[] = "{$tab}use {$hint};";
        }
        if (0 < count($lines)) {
            $lines[] = '';
        }
        return $lines;
    }
}
