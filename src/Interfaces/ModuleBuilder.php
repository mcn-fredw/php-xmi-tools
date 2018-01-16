<?php
namespace XMITools\Interfaces;

/**
 * Interface ModuleBuilder
 * Object for building a module.
 */
interface ModuleBuilder
{
    /**
     * Creates a new module and stores it in the module store.
     */
    public function createModule(
        XMIReader $reader,
        ModuleStore $store
    );

    /**
     * Gets fully qualified module name.
     * @return string
     */
    public function fullName();

    /**
     * Implemented in class to get reader nodes that pertain to
     * the module type.
     * @param Interfaces\XMIReader $reader Current node is for the module.
     */
    public function gatherElements(XMIReader $reader);

    /**
     * Gets abstraction consumer for module.
     * @return ?AbstractionConsumer implementation.
     */
    public function getAbstractionConsumer();

    /**
     * Gets method importer for module.
     * @return ?MethodImporter implementation.
     */
    public function getMethodImporter();

    /**
     * Gets module writer for module.
     * @return ?ModuleWriter implementation.
     */
    public function getModuleWriter();

    /**
     * Gets source code attribute importer for module.
     * @return ?SourceCodeAttributeImporter implementation.
     */
    public function getSourceCodeAttributeImporter();

    /**
     * Gets source code constant importer for module.
     * @return ?SourceCodeConstantImporter implementation.
     */
    public function getSourceCodeConstantImporter();

    /**
     * Gets source code method importer for module.
     * @return ?SourceCodeMethodImporter implementation.
     */
    public function getSourceCodeMethodImporter();

    /**
     * Gets source code trait importer for module.
     * @return ?SourceCodeTraitImporter implementation.
     */
    public function getSourceCodeTraitImporter();

    /**
     * Gets template parameter consumer for module.
     * @return ?TemplateParameterConsumer implementation.
     */
    public function getTemplateParameterConsumer();

    /**
     * Gets test generator for module.
     * @return ?TestGenerator implementation.
     */
    public function getTestGenerator();

    /**
     * Gets type importer for module.
     * @return ?TypeImporter implementation.
     */
    public function getTypeImporter();

    /**
     * Gets a target name for a use statement
     * to import this object in to owning namespace.
     * @param string $owning Namespace for the module doing the import.
     * @return string
     */
    public function importFor($owning);

    /**
     * Gets a type hint for this.
     * @param string $owning Namespace for the using module.
     * @return string
     */
    public function typeHintFor($owning);
}
