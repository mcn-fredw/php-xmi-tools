<?php
namespace XMITools;

/**
 * Class BaseBuilder
 * Base Interfaces\ModuleBuilder implementation.
 */
abstract class BaseModuleBuilder extends Module implements
    Interfaces\ModuleBuilder
{
    /**
     * Callback to create a module.
     * @param Interfaces\XMIReader $reader Current node is for the module.
     * @param Interfaces\ModuleStore $store Where to save the created module.
     */
    public static function create(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleStore $store
    ) {
        $type = 'class';
        if (0 < strlen($reader->stereotype())) {
            $type = $reader->stereotype();
        }
        $typeKey = $type . '-module-builder';
        $moduleClass = FactoryService::get($typeKey);
        $builder = new $moduleClass();
        $builder->createModule($reader, $store);
    }

    /**
     * {@inheritDoc}
     */
    public function createModule(
        Interfaces\XMIReader $reader,
        Interfaces\ModuleStore $store
    ) {
        $this->xmiId($reader->xmiId());
        $this->name($reader->shortName());
        $this->ns($reader->namespaceOf($reader->fullName()));
        $this->stereotype($reader->stereotype());
        $this->isAbstract($reader->isAbstract());
        $value = $reader->comment();
        if (0 < strlen($value)) {
            $this->comment($value);
        }
        $this->gatherElements($reader);
        $store->saveModule($this);
        echo sprintf(
            "Created module for %s\n",
            $this->fullName()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getAbstractionConsumer()
    {
        if ($this instanceof Interfaces\AbstractionConsumer) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodImporter()
    {
        if ($this instanceof Interfaces\MethodImporter) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleWriter()
    {
        if ($this instanceof Interfaces\ModuleWriter) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceCodeAttributeImporter()
    {
        if ($this instanceof Interfaces\SourceCodeAttributeImporter) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceCodeConstantImporter()
    {
        if ($this instanceof Interfaces\SourceCodeConstantImporter) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceCodeMethodImporter()
    {
        if ($this instanceof Interfaces\SourceCodeMethodImporter) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceCodeTraitImporter()
    {
        if ($this instanceof Interfaces\SourceCodeTraitImporter) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateParameterConsumer()
    {
        if ($this instanceof Interfaces\TemplateParameterConsumer) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTestable()
    {
        if ($this instanceof Interfaces\Testable) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeImporter()
    {
        if ($this instanceof Interfaces\TypeImporter) {
            return $this;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function importFor($owning)
    {
        if (substr($owning, -1) !== '\\') {
            $owning .= '\\';
        }
        $fn = $this->fullName();
        $len = strlen($owning);
        $cmp = strncmp($fn, $owning, $len);
        if ($cmp === 0) {
            return '';
        }
        return $fn;
    }

    /**
     * {@inheritDoc}
     */
    public function isAbstractClass()
    {
        return ($this->isAbstract() && $this->isClass());
    }

    /**
     * {@inheritDoc}
     */
    public function isClass()
    {
        return false;
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
    public function isTrait()
    {
        return false;
    }


    /**
     * {@inheritDoc}
     */
    public function typeHintFor($owning)
    {
        if (substr($owning, -1) !== '\\') {
            $owning .= '\\';
        }
        $fn = $this->fullName();
        $len = strlen($owning);
        $cmp = strncmp($fn, $owning, $len);
        if ($cmp === 0) {
            return substr($fn, $len);
        }
        return $this->name();
    }
}
