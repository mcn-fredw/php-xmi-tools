<?php
namespace XMITools;

use UnexpectedValueException;
use OutOfBoundsException;
use ReflectionClass;

/**
 * class ModuleCollector
 * Implementation of module collector,
 * module store +,
 * and path translator.
 */
class ModuleCollector implements
    Interfaces\ModuleCollector,
    Interfaces\PathTranslator
{
    /**
     * Maps xmi id to module object.
     * @var array
     */
    protected $modules = [];
    /**
     * Composer json data for project.
     * @var array
     */
    protected $autoloaders = [];
    /**
     * Base path of project directory.
     * @var string
     */
    protected $projectPath;

    /**
     * Initialize instance.
     * @param array $ops Command line options.
     */
    public function __construct($ops)
    {

        $this->projectPath = $ops['project-dir'];
        $this->loadAutoloaders();
        $composerFile = $this->projectPath . '/composer.json';
        $this->projectJson = json_decode(
            file_get_contents($composerFile),
            true
        );
    }

    /**
     * Autoloader for existing module files.
     * @param string $fullName Full name space name of module.
     * @post require_once() called to load the module file, if it exists.
     */
    public function autoloadModule($fullName)
    {
        $path = $this->pathForName($fullName, false);
        if (is_file($path)) {
            require_once($path);
        }
    }

    /**
     * Callback to bind an abstraction.
     * @param Interfaces\XMIReader $reader Has abstraction node.
     * @param Interfaces\ModuleCollector $collector Pass through.
     */
    public function bindAbstraction(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleCollector $collector
    ) {
        $supplierId = $reader->supplier();
        $clientId = $reader->client();
        if (0 == strlen($supplierId)) {
            $supplierId = $reader->parent();
            $clientId = $reader->child();
        }
        if ($this->checkIds($clientId, $supplierId)) {
            $this->resolveAbstractionConsumer(
                $this->modules[$supplierId],
                $this->modules[$clientId]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function buildModules(
        Interfaces\XMIReader $reader
    ) {
        $moduleFactory = FactoryService::get('module-factory');
        $reader->walkDatatypes($moduleFactory, $this);
        $reader->walkInterfaces($moduleFactory, $this);
        $reader->walkClasses($moduleFactory, $this);
        $reader->walkAbstractions([$this, 'bindAbstraction'], $this);
    }

    /**
     * Asks abstraction consumer to consume an abstraction.
     * @param Interfaces\Module $abstraction
     * @param Interfaces\AbstractionConsumer $consumer
     */
    protected function callAbstractionBinder(
        Interfaces\Module $abstraction,
        Interfaces\AbstractionConsumer $consumer = null
    ) {
        if ($consumer) {
            $consumer->bindAbstraction($abstraction);
        }
    }

    /**
     * Asks method importer to import methods.
     * @param Interfaces\MethodImporter $importer
     */
    protected function callMethodImporter(
        Interfaces\MethodImporter $importer = null
    ) {
        if ($importer) {
            $importer->importMethods($this);
        }
    }

    /**
     * Asks module writer to write module.
     * @param Interfaces\ModuleWriter $writer
     */
    protected function callModuleWriter(
        Interfaces\ModuleWriter $writer = null
    ) {
        if ($writer) {
            $writer->writeModule($this, $this);
        }
    }

    /**
     * Asks module to import attributes from existing source code.
     * @param ReflectionClass $class
     * @param array &$lines Module file lines.
     * @param Interfaces\SourceCodeAttributeImporter $importer
     */
    protected function callSourceCodeAttributeImporter(
        ReflectionClass $class,
        array &$lines,
        Interfaces\SourceCodeAttributeImporter $importer = null
    ) {
        if ($importer) {
            $defaults = $class->getDefaultProperties();
            foreach ($class->getProperties() as $reflection) {
                $importer->importSourceCodeAttribute(
                    $reflection,
                    $defaults,
                    $this,
                    $lines
                );
            }
        }
    }

    /**
     * Asks module to import constants from existing source code.
     * @param ReflectionClass $class
     * @param Interfaces\SourceCodeConstantImporter $importer
     */
    protected function callSourceCodeConstantImporter(
        ReflectionClass $class,
        Interfaces\SourceCodeConstantImporter $importer = null
    ) {
        if ($importer) {
            foreach ($class->getReflectionConstants() as $reflection) {
                $importer->importSourceCodeConstant(
                    $reflection,
                    $this
                );
            }
        }
    }

    /**
     * Asks module to import methods from existing source code.
     * @param ReflectionClass $class
     * @param array &$lines Module file lines.
     * @param Interfaces\SourceCodeMethodImporter $importer
     */
    protected function callSourceCodeMethodImporter(
        ReflectionClass $class,
        array &$lines,
        Interfaces\SourceCodeMethodImporter $importer = null
    ) {
        if ($importer) {
            foreach ($class->getMethods() as $reflection) {
                $fn = $this->pathForName($importer->fullName());
                if ($reflection->getFileName() != $fn) {
                    continue;
                }
                $importer->importSourceCodeMethod(
                    $reflection,
                    $this,
                    $lines
                );
            }
        }
    }

    /**
     * Asks module to import traits from existing source code.
     * @param ReflectionClass $class
     * @param array &$lines Module file lines.
     * @param Interfaces\SourceCodeTraitImporter $importer
     */
    protected function callSourceCodeTraitImporter(
        ReflectionClass $class,
        array &$lines,
        Interfaces\SourceCodeTraitImporter $importer = null
    ) {
        if ($importer) {
            foreach ($class->getTraits() as $reflection) {
                $importer->importSourceCodeTrait(
                    $reflection,
                    $this,
                    $lines
                );
            }
        }
    }

    /**
     * Asks test generator to generate tests for module.
     * @param Interfaces\TestGenerator $importer
     */
    protected function callTestGenerator(
        Interfaces\ModuleBuilder $builder,
        Interfaces\Testable $testable = null
    ) {
        if ($testable  && $testable->hasTests()) {
            $factory = FactoryService::get('test-class-builder');
            call_user_func($factory, $builder, $this);
        }
    }

    /**
     * Asks type importer to import types.
     * @param Interfaces\TypeImporter $importer
     */
    protected function callTypeImporter(
        Interfaces\TypeImporter $importer = null
    ) {
        if ($importer) {
            $importer->importTypes($this, $importer);
        }
    }

    /**
     * Helper function to check XML id(s) are valid.
     * @param vararg $id One or more XML id(s) to validate.
     * @return bool false if any one of the ids are invalid,
     * true otherwise.
     */
    protected function checkIds()
    {
        foreach(func_get_args() as $id) {
            if (! isset($this->modules[$id])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Creates and writes modules from XMI doc.
     * @param array $ops Command line options from main().
     */
    public static function createFromXmi($ops)
    {
        $collector = new static($ops);
        $xmiReaderFactory = FactoryService::get('xmi-doc-reader');
        $xmiReader = call_user_func($xmiReaderFactory, $ops);
        $collector->buildModules($xmiReader);
        $collector->mapTypes();
        $collector->generateTests();
        $collector->writeModules();
    }

    /**
     * {@inheritDoc}
     */
    public function createDirectories($path)
    {
        $dirs = dirname($path);
        @mkdir($dirs, 0755, true);
        return $path;
    }

    /**
     * Converts a namespace relative path to a full path
     * and makes sure the directories exist.
     * @param string $name Namespace relative path.
     * @return string Full module path.
     * @post Intermediate directories are created if they don't exist.
     */
    protected function createPath($name)
    {
        $name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        $path = $this->projectPath . DIRECTORY_SEPARATOR . $name . '.php';
        return $this->createDirectories($path);
    }

    /**
     * {@inheritDoc}
     */
    public function generateTests()
    {
        $this->visitModules([$this, 'resolveTestGenerator']);
    }

    /**
     * {@inheritDoc}
     */
    public function getModule($id)
    {
        if (! array_key_exists($id, $this->modules)) {
            throw new OutOfBoundsException(
                $id . ' is an unknown module id'
            );
        }
        return $this->modules[$id];
    }

    /**
     * {@inheritDoc}
     */
    public function importFor($ns, $hint)
    {
        foreach ($this->modules as $module) {
            if ($module->typeHintFor($ns) == $hint) {
                return $module->importFor($ns);
            }
        }
        return $hint;
    }

    /**
     * Checks prefix is a known psr-4 namespace prefix.
     * @param string $prefix
     * @param string $file Part of the name that is a module file name.
     * @return string empty string prefix is not a psr-4 prefix.
     * Otherwise; module's full path name, including the .php extension.
     * @post Modules directories are created when returning the full path.
     */
    protected function isPsr4Prefix($prefix, $file)
    {
        if (isset($this->autoloaders[$prefix])) {
            $dirs = (array)$this->autoloaders[$prefix];
            return $this->createPath(
                strrev($file . strrev(reset($dirs)))
            );
        }
        return '';
    }

    /**
     * Loads project auto loader spec from composer.json file.
     * @post $this->autoloaders is the array of autoloaders.
     */
    protected function loadAutoloaders()
    {
        $composerFile = $this->projectPath . '/composer.json';
        $json = json_decode(file_get_contents($composerFile), true);
        if(! isset($json['autoload']['psr-4'])) {
            /* none to work with */
            return;
        }
        $loaders[] = $json['autoload']['psr-4'];
        if(isset($json['autoload-dev']['psr-4'])) {
            /* grab dev loader for test classes */
            $loaders[] = $json['autoload-dev']['psr-4'];
        }
        foreach ($loaders as $loader) {
            foreach ($loader as $match => $value) {
                /* reverse for positional scanning */
                $this->autoloaders[strrev($match)] = $value;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function mapTypes()
    {
        $this->visitModules([$this, 'resolveMethodImporter']);
        $this->visitModules([$this, 'resolveTypeImporter']);
    }

    /**
     * {@inheritDoc}
     */
    public function pathForName($name, $create = true)
    {
        if (0 < count($this->autoloaders)) {
            if ($path = $this->translatePsr4Path($name)) {
                return $path;
            }
        }
        /* no autoload path to work with */
        if ($create) {
            return $this->createPath($name);
        }
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function projectPath()
    {
        return $this->projectPath;
    }

    /**
     * Helper step to get abstraction consumer from module.
     * @param Interfaces\Module $abstraction
     * @param Interfaces\Module $module
     */
    protected function resolveAbstractionConsumer(
        Interfaces\Module $abstraction,
        Interfaces\Module $module
    ) {
        $this->callAbstractionBinder(
            $abstraction,
            $module->builder()->getAbstractionConsumer()
        );
    }

    /**
     * Gets method importer from module.
     * @param Interfaces\Module $module
     */
    protected function resolveMethodImporter(
        Interfaces\ModuleBuilder $builder
    ) {
        $this->callMethodImporter($builder->getMethodImporter());
    }

    /**
     * Gets module writer from module.
     * @param Interfaces\Module $module
     */
    protected function resolveModuleWriter(
        Interfaces\ModuleBuilder $builder
    ) {
        $this->callModuleWriter($builder->getModuleWriter());
    }

    /**
     * Gets test generator from module.
     * @param Interfaces\Module $module
     */
    protected function resolveSourceCodeImporter(
        Interfaces\ModuleBuilder $builder
    ) {
        if (! $builder->getModuleWriter()) {
            /* module won't import source code */
            return;
        }
        $name = $builder->fullName();
        $path = $this->pathForName($name, false);
        if (! is_file($path)) {
            return;
        }
        $lines = file($path);
        $class = new ReflectionClass($name);
        $this->callSourceCodeConstantImporter(
            $class,
            $builder->getSourceCodeConstantImporter()
        );
        $this->callSourceCodeAttributeImporter(
            $class,
            $lines,
            $builder->getSourceCodeAttributeImporter()
        );
        $this->callSourceCodeTraitImporter(
            $class,
            $lines,
            $builder->getSourceCodeTraitImporter()
        );
        $this->callSourceCodeMethodImporter(
            $class,
            $lines,
            $builder->getSourceCodeMethodImporter()
        );
    }

    /**
     * Gets test generator from module.
     * @param Interfaces\Module $module
     */
    protected function resolveTestGenerator(
        Interfaces\ModuleBuilder $builder
    ) {
        $this->callTestGenerator($builder, $builder->getTestable());
    }

    /**
     * Gets type importer from module.
     * @param Interfaces\Module $module
     */
    protected function resolveTypeImporter(
        Interfaces\ModuleBuilder $builder
    ) {
        $this->callTypeImporter($builder->getTypeImporter());
    }

    /**
     * {@inheritDoc}
     */
    public function saveModule(
        Interfaces\Module $module
    ) {
        $this->modules[$module->xmiId()] = $module;
    }

    /**
     * Performs psr-4 translation on a module namespace name.
     * @param string $name Module fully qualified name.
     * @return string Full path for name if a matching prefix is found.
     * An empty string is returned if no matching prefix is found.
                $this->autoloaders[strrev($match)] = $value;
     */
    protected function translatePsr4Path($name)
    {
        /*
         * match from deepest to shallowest part of name.
         * using reversed string means we only need to
         * track the position we used.
         */
        $rName = strrev($name);
        $pos = strpos($rName, '\\', 0);
        while (false !== $pos) {
            $match = $this->isPsr4Prefix(
                substr($rName, $pos),
                substr($rName, 0, $pos)
            );
            if ($match) {
                return $match;
            }
            $pos = strpos($rName, '\\', $pos + 1);
        }
        /* check for a default path */
        return $this->isPsr4Prefix('', $rName);
    }

    /**
     * Calls a callback for each module.
     * @param callable {
     *     @param Interfaces\Module $module
     * } $callback
     */
    protected function visitModules($callback)
    {
        foreach ($this->modules as $module) {
            call_user_func($callback, $module->builder());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function writeModules()
    {
        spl_autoload_register([ $this, 'autoloadModule']);
        $this->visitModules([$this, 'resolveSourceCodeImporter']);
        $this->visitModules([$this, 'resolveModuleWriter']);
    }
}
