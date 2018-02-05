<?php
namespace XMITools\Interfaces;

/**
 * Interface TestClassBuilder
 * Public API for builder that builds a test class.
 * Test sockets are classes or interfaces added to the test class
 * in order to extend the functionality of the class or trait under test.
 * Example use:
 * Traits need to be wrapped in a class to be instantiated.
 * Creating a class builder and telling it to use the trait
 * allows the test class method to instantiated the socket class
 * to test the trait.
 * Interface sockets are handy for mocking things that are otherwise
 * difficult to mock.
 * Interface and concrete method builders
 * have a Builder->reflected() accessor,
 * which ca be set to an array of source code lines (sans new lines).
 */
interface TestClassBuilder extends Module, ModuleBuilder
{
    /**
     * Stash import for type hint in test class.
     * @param string $hint Type hint to look up.
     * @param ModuleBuilder $builder Module containing the import.
     * @param ModuleStore $store Type lookup.
     */
    public function addBuilderImport(
        $hint,
        ModuleBuilder $builder,
        ModuleStore $store
    );

    /**
     * Adds use import to test class.
     * @param string $fullName Full name space name of import.
     * @param string $hint Short name of import.
     */
    public function addTestImport($fullName, $hint);

    /**
     * Adds a method to the test class.
     * If the method already exists, it will be replaced.
     * Comparison is done by method name.
     * @param MethodBuilder $method Method to add to test class.
     */
    public function addTestMethod(MethodBuilder $method);

    /**
     * Adds a test socket class or interface to the test
     * class module..
     * If the socket already exists, it will be replaced.
     * Comparison is done by method name.
     * @param ModuleBuilder $socket Test socket to include
     * in test class module.
     * The socket name is set with $socket->name($name).
     * @return null|ModuleBuilder
     */
    public function addTestSocket(ModuleBuilder $socket);

    /**
     * Adds trait to test class.
     * @param string $fullName Full name space name of trait.
     * @param string $useName Use name for trait.
     */
    public function addTestTrait($fullName, $useName);

    /**
     * Adds a callback to the test class builder that will be called
     * after the test class is written.
     * @param callable {
     *   @param TestClassBuilder $testClass,
     *   @param ModuleStore $store,
     *   @param PathTranslator $paths,
     *   @param mixed $data
     * } $calllback.
     * @param mixed $data Pass through data for callback.
     */
    public function addTestWriteHook($callback, $data);

    /**
     * Gets test socket class or interface.
     * @param string $socketName Hint type name
     * for test socket class or interface.
     * @return null|ModuleBuilder
     */
    public function getTestSocket($socketName);
}
