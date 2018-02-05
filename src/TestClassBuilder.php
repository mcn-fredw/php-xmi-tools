<?php
namespace XMITools;

use Symfony\Component\Yaml\Yaml;

/**
 * Class TestClassBuilder
 * Builds tests for classes and traits.
 */
class TestClassBuilder extends ClassBuilder implements
    Interfaces\TestClassBuilder
{
    /** Array of interface/class builders output as stubs. */
    protected $sockets = [];
    /** Callbacks called after test class is written. */
    protected $writeHooks = [];
    /** Base test class. */
    protected $baseTestClass = 'PHPUnit\Framework\TestCase';

    /**
     * {@inheritDoc}
     */
    public function addBuilderImport(
        $hint,
        Interfaces\ModuleBuilder $builder,
        Interfaces\ModuleStore $store
    ) {
        $len = strlen($hint);
        foreach ($builder->imports() as $id => $fname) {
            if (substr($fname, -$len) !== $hint) {
                continue;
            }
            $slash = $len - 1;
            if (
                strlen($fname) != $len
                && substr($fname, -$slash, 1) !== "\\"
            ) {
                /* partial match not on package boundary */
                continue;
            }
            $type = $store->getModule($id);
            $import = $type->importFor($this->ns);
            if (0 < strlen($import)) {
                $this->imports[$id] = $import;
            }
            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addTestImport($fullName, $hint)
    {
        $this->imports[$hint] = $fullName;
    }

    /**
     * {@inheritDoc}
     */
    public function addTestMethod(
        Interfaces\MethodBuilder $method
    ) {
        $this->methods[$method->name()] = $method;
    }

    /**
     * {@inheritDoc}
     */
    public function addTestSocket(
        Interfaces\ModuleBuilder $socket
    ) {
        $this->sockets[$socket->name()] = $socket;
    }

    /**
     * {@inheritDoc}
     */
    public function addTestTrait($fullName, $useName)
    {
        $this->bindAbstractionData(
            true,
            $this->traits,
            $useName,
            $fullName,
            $useName
        );
    }

    /**
     * {@inheritDoc}
     */
    public function addTestWriteHook($callback, $data)
    {
        $this->writeHooks[] = [
            'fn' => $callback,
            'data' => $data
        ];
    }

    /**
     * Adds a @test annotation to builder comments.
     * @param Interfaces\ModuleBuilder $builder Module test is for.
     */
    protected function appendTestAnnotation(
        Interfaces\ModuleBuilder $builder
    ) {
        $comment = $builder->comment();
        if (0 == strlen($comment)) {
            $builder->comment(sprintf('@test %s', $this->fullName()));
            return;
        }
        $builder->comment(
            sprintf("%s\n@test %s", $comment, $this->fullName())
        );
    }

    /**
     * Calls the test write hooks.
     * @param Interfaces\ModuleStore $store
     * @param Interfaces\PathTranslator $paths
     */
    protected function callWriteHooks(
        Interfaces\ModuleStore $store,
        Interfaces\PathTranslator $paths
    ) {
        foreach ($this->writeHooks as $hook) {
            call_user_func(
                $hook['fn'],
                $this,
                $store,
                $paths,
                $hook['data']
            );
        }
    }

    /**
     * Callback to create a test class module.
     * @param Interfaces\ModuleBuilder $builder Module test is for.
     * @param Interfaces\ModuleStore $store Where to save the created module.
     */
    public static function createTestClass(
        Interfaces\ModuleBuilder $builder,
        Interfaces\ModuleStore $store
    ) {
        $test = new static();
        $test->createTests($builder, $store);
        $store->saveModule($test);
    }

    /**
     * Callback to create tests for builder module.
     * @param Interfaces\ModuleBuilder $builder Module test is for.
     * @param Interfaces\ModuleStore $store Where to save the created module.
     */
    public function createTests(
        Interfaces\ModuleBuilder $builder,
        Interfaces\ModuleStore $store
    ) {
        $driversBase = 'TestDrivers\\';
        $this->xmiId($builder->xmiId() . '-tests');
        $this->name($builder->name() . 'Test');
        $parts = explode("\\", $builder->ns());
        $parts[0] .= 'Tests';
        $this->ns(implode("\\", $parts));
        $this->stereotype('');
        $this->isAbstract(false);
        $params = [
            'factory' => __NAMESPACE__ . '\FactoryService::get',
            'class' => $this,
            'for'   => $builder,
            'test' => null,
            'data'  => null,
            'store' => $store,
            'paths' => $store
        ];
        foreach ($builder->tests() as $attribute) {
            $parts = explode(',', $attribute->value());
            $kind = reset($parts);
            $driverName = $driversBase . $kind;
            $driverPath  = substr($store->pathForName($driverName), 0, -4);
            $params['data'] = Yaml::parse(file_get_contents($driverPath));
            $params['test'] = $attribute;
            $creator = $params['data']['builder-factory'];
            if (is_callable($creator)) {
                call_user_func($creator, $params);
            }
        }
        /* add test class annotation to module comment */
        $this->appendTestAnnotation($builder);
        /* extend php unit test class and use DbUnit if needed */
        $this->finishTestClassImports($builder, $store);
    }

    /**
     * Adds a @test annotation to builder comments.
     * @param Interfaces\ModuleBuilder $builder Module containing the import.
     */
    protected function finishTestClassImports(
        Interfaces\ModuleBuilder $builder,
        Interfaces\ModuleStore $store
    ) {
        $this->imports[$builder->xmiId()] = $builder->importFor($this->ns);
        $supplier = $store->findModule($this->baseTestClass);
        if ($supplier) {
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
        } else {
            $parts = explode("\\", $this->baseTestClass);
            $hint = array_pop($parts);
            $this->bindAbstractionData(
                true,
                $this->extends,
                $hint,
                $this->baseTestClass,
                $hint
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTestSocket($socketName)
    {
        if (isset($this->sockets[$socketName])) {
            return $this->sockets[$socketName];
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function moduleStubs()
    {
        $lines = [];
        foreach ($this->sockets as $socket) {
            array_splice(
                $lines,
                count($lines),
                0,
                $socket->moduleSourceCode(true)
            );
        }
        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    public function writeModule(
        Interfaces\ModuleStore $store,
        Interfaces\PathTranslator $paths
    ) {
        parent::writeModule($store, $paths);
        $this->callWriteHooks($store, $paths);
    }
}
