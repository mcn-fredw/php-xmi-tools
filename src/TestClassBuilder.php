<?php
namespace XMITools;

/**
 * Class TestClassBuilder
 * Builds tests for classes and traits.
 */
class TestClassBuilder extends ClassBuilder
{
    protected $stubs = [];

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
        $this->xmiId($builder->xmiId() . '-tests');
        $this->name($builder->shortName() . 'Tests');
        $parts = explode("\\", $builder->ns());
        $parts[0] .= 'Tests';
        $this->ns(implode("\\", $parts));
        $this->stereotype('');
        $this->isAbstract(false);
        
    }

    /**
     * {@inheritDoc}
     */
    protected function moduleStubs()
    {
        return [];
    }

}
