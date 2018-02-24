<?php
namespace XMITools;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DoctrineEnitityBuilder
 * Builder for doctrine entities.
 */
class DoctrineEnitityBuilder extends ClassBuilder
{
    const API_ANNOTATION = '@api';
    const API_PATH_FORMAT = "%s/%s.yaml";
    const API_TAG = '@api-yaml';
    const COLLECTION_OPS_KEY = 'collectionOperations';
    const DUMMY_PATH_ELEMENT = 'dummy';
    const ID_KEY = 'id';
    const IDENTIFIER_KEY = 'identifier';
    const ITEM_OPS_KEY = 'itemOperations';
    const FIELDS_KEY = 'fields';
    const ORM_ANNOTATION = '@orm';
    const ORM_PATH_FORMAT = "%s/%s.orm.yml";
    const ORM_TAG = '@orm-yaml';
    const REPO_ANNOTATION = '@repository';
    const REPO_DIR = 'Repository';
    const REQUIREMENTS_KEY = 'requirements';
    const PERMS_ANNOTATION = '@perms';
    const PERMS_PATH_FORMAT = "%s/%s.yaml";
    const PERMS_TAG = '@perms-yaml';
    const PROPERTIES_KEY = 'properties';
    const TABLE_ANNOTATION = '@table';

    /** @var null|array orm yaml mapping. */
    protected $ormYaml;
    /** @var null|array api yaml mapping. */
    protected $apiYaml;
    /** @var null|array permissions yaml mapping. */
    protected $permsYaml;
    protected static $intRegex;
    protected static $guidRegex;

    /**
     * Appends elements to api yaml array for attribute.
     * @param Interfaces\AttributeBuilder $attribute
     * @post $this->apiYaml updated.
     */
    protected function apiAppendYaml(
        Interfaces\AttributeBuilder $attribute
    ) {
        if (! isset($this->annotations[self::API_ANNOTATION])) {
            return;
        }
        $meta = $this->extractYaml($attribute, self::API_TAG);
        if (0 == count($meta)) {
            /* no lines to work with */
            return;
        }
        /* make sure api yaml is setup */
        $this->apiSetupYaml();
        $field = $attribute->name();
        if (is_array($this->ormYaml)
            && isset($this->ormYaml[self::ID_KEY][$field])
        ) {
            if (! isset($meta[self::IDENTIFIER_KEY])) {
                $meta[self::IDENTIFIER_KEY] = true;
            }
            /* add requirements to item ops for field */
            $sec = self::ITEM_OPS_KEY;
            $req = self::REQUIREMENTS_KEY;
            foreach (array_keys($this->apiYaml[$sec]) as $key) {
                $regex = $this->apiRegex($field);
                $this->apiYaml[$sec][$key][$req][$field] = $regex;
            }
        }
        $this->apiYaml[self::PROPERTIES_KEY][$field]= $meta;
    }

    /**
     * Gets regex for identifier column.
     * @param string $field Field to get regex for.
     * @return string
     * @pre Assumes $this->ormYaml['id'] exists.
     */
    protected function apiRegex($field)
    {
        if (0 == strlen(self::$intRegex)) {
            self::$intRegex = '^\d+$';
        }
        if (0 == strlen(self::$guidRegex)) {
            self::$guidRegex = '^[0-9a-fA-F]{8}'
                . '-[0-9a-fA-F]{4}'
                . '-[1-5][0-9a-fA-F]{3}'
                . '-[89abAB][0-9a-fA-F]{3}'
                . '-[0-9a-fA-F]{12}$';
        }
        switch ($this->ormYaml[self::ID_KEY][$field]['type']) {
            case 'int':
                return self::$intRegex;
            case 'guid':
                return self::$guidRegex;
        }
        return '^.+$';
    }

    /**
     * Sets up the api yaml array.
     * @post $this->apiYaml updated.
     */
    protected function apiSetupYaml()
    {
        if (! isset($this->annotations[self::API_ANNOTATION])
            || is_array($this->apiYaml)
        ) {
            return;
        }
        $this->apiYaml = [
            'shortName' => $this->name(),
            self::ITEM_OPS_KEY => [],
            self::COLLECTION_OPS_KEY => [],
            self::PROPERTIES_KEY => []

        ];
        $cmt = $this->annotations[self::API_ANNOTATION]->comment();
        if (0 < strlen($cmt)) {
            $meta = Yaml::parse($cmt);
            foreach ($meta as $key => $value) {
                $this->apiYaml[$key] = $value;
            }
            return;
        }
        $this->apiYaml[self::ITEM_OPS_KEY] = [
            'get' => [ 'method' => 'GET' ],
            'put' => [ 'method' => 'PUT' ],
        ];
        $this->apiYaml[self::COLLECTION_OPS_KEY] = [
            'get'  => [ 'method' => 'GET' ],
            'post' => [ 'method' => 'POST' ],
        ];
    }

    /**
     * Appends yaml section of attribute comments to one of the yaml arrays.
     * @param array &$src Reference to attributes list.
     * @param Interfaces\ModuleStore $store For module lookup.
     */
    protected function appendAttributeFields(
        array &$src,
        Interfaces\ModuleStore $store
    ) {
        foreach ($src as $attribute) {
            $this->ormAppendYaml($attribute);
            $this->apiAppendYaml($attribute);
            $this->permsAppendYaml($attribute);
        }
    }

    /**
     * Appends yaml section of trait attribute comments to yaml arrays.
     * @param Interfaces\ModuleStore $store For module lookup.
     */
    protected function appendTraitFields(
        Interfaces\ModuleStore $store
    ) {
        foreach ($this->traits as $tKey => $tHint) {
            if ($tKey == $tHint) {
                continue;
            }
            $module = $store->getModule($tKey);
            $this->appendAttributeFields(
                $module->attributes,
                $store
            );
        }
    }

    /**
     * Extract yaml section from attribute comments.
     * @param Interfaces\AttributeBuilder $attribute
     * Attribute to extract lines from.
     * @param string $tag Comments section tag.
     * @return array
     */
    protected function extractYaml(
        Interfaces\AttributeBuilder $attribute,
        $tag
    ) {
        $cmt = explode("\n", $attribute->comment());
        $more = true;
        $n = count($cmt);
        $i = 0;
        while ($i < $n && $more) {
            /* find line after first occurrence of tag */
            $more = (false === strpos($cmt[$i++], $tag));
        }
        $meta = [];
        while ($i < $n && false === strpos($cmt[$i], $tag)) {
            /* collect until second occurrence of tag */
            $meta[] = $cmt[$i++];
        }
        if (0 < count($meta)) {
            $meta = Yaml::parse(implode("\n", $meta));
        }
        return $meta;
    }

    /**
     * Appends elements to orm yaml array for attribute.
     * @param Interfaces\AttributeBuilder $attribute
     * @post $this->ormYaml updated.
     */
    protected function ormAppendYaml(
        Interfaces\AttributeBuilder $attribute
    ) {
        if (! isset($this->annotations[self::ORM_ANNOTATION])) {
            return;
        }
        $meta = $this->extractYaml($attribute, self::ORM_TAG);
        if (0 == count($meta)) {
            /* no lines to work with */
            return;
        }
        /* make sure orm yaml is setup */
        $this->ormSetupYaml();
        $field = $attribute->name();
        if (array_key_exists($field, $this->ormYaml[self::ID_KEY])) {
            $this->ormYaml[self::ID_KEY][$field] = $meta;
            return;
        }
        $this->ormYaml[self::FIELDS_KEY][$field] = $meta;
    }

    /**
     * Builds base element of orm yaml.
     * @post $this->ormYaml updated.
     */
    protected function ormGetBaseSection()
    {
        return [
            'type' => 'entity',
            'table' => $this->annotations[self::TABLE_ANNOTATION]->value(),
            'repositoryClass' => $this->ormGetRepository(),
            self::ID_KEY => [],
            self::FIELDS_KEY => []
        ];
    }

    /**
     * Gets orm entity repository.
     * @return string
     */
    protected function ormGetRepository()
    {
        $repo = $this->annotations[self::REPO_ANNOTATION]->value();
        if (false === strpos($repo, "\\")) {
            $repo = implode(
                "\\",
                [
                    explode("\\", $this->ns)[0],
                    self::REPO_DIR,
                    $repo
                ]
            );
        }
        return $repo;
    }

    /**
     * Sets up the orm yaml array.
     * @post $this->ormYaml updated.
     */
    protected function ormSetupYaml()
    {
        if (! isset($this->annotations[self::ORM_ANNOTATION])
            || is_array($this->ormYaml)
        ) {
            return;
        }
        $this->ormYaml = $this->ormGetBaseSection();
        $meta = Yaml::parse(
            $this->annotations[self::ORM_ANNOTATION]->comment()
        );
        foreach ($meta as $key => $value) {
            $this->ormYaml[$key] = $value;
        }
    }

    /**
     * Appends elements to permissions yaml array for attribute.
     * @param Interfaces\AttributeBuilder $attribute
     * @post $this->permsYaml updated.
     */
    protected function permsAppendYaml(
        Interfaces\AttributeBuilder $attribute
    ) {
        if (! isset($this->annotations[self::PERMS_ANNOTATION])) {
            return;
        }
        $meta = $this->extractYaml($attribute, self::PERMS_TAG);
        if (0 == count($meta)) {
            /* no lines to work with */
            return;
        }
        /* make sure api yaml is setup */
        $this->permsSetupYaml();
        $field = $attribute->name();
        $this->permsYaml[$field] = $meta;
    }

    /**
     * Sets up the permisssions yaml array.
     * @post $this->permsYaml updated.
     */
    protected function permsSetupYaml()
    {
        if (! isset($this->annotations[self::PERMS_ANNOTATION])
            || is_array($this->permsYaml)
        ) {
            return;
        }
        $this->permsYaml = [];
    }

    /**
     * {@inheritDoc}
     */
    public function writeModule(
        Interfaces\ModuleStore $store,
        Interfaces\PathTranslator $paths
    ) {
        parent::writeModule($store, $paths);
        $this->appendAttributeFields($this->attributes, $store);
        $this->appendTraitFields($store);
        $this->writeYaml(
            $store,
            $paths,
            self::ORM_ANNOTATION,
            self::ORM_PATH_FORMAT,
            $this->ormYaml
        );
        $this->writeYaml(
            $store,
            $paths,
            self::API_ANNOTATION,
            self::API_PATH_FORMAT,
            $this->apiYaml
        );
        $this->writeYaml(
            $store,
            $paths,
            self::PERMS_ANNOTATION,
            self::PERMS_PATH_FORMAT,
            $this->permsYaml
        );
        $this->apiYaml = null;
        $this->ormYaml = null;
        $this->permsYaml = null;
    }

    /**
     * Writes yaml orm metadata for entity.
     * @param Interfaces\ModuleStore $store
     * @param Interfaces\PathTranslator $paths
     * @param string $annotation Annotation attribute name.
     * @param string $pathFormat Metatdata file path format.
     * @param array &$yaml Yaml array to output.
     */
    protected function writeYaml(
        Interfaces\ModuleStore $store,
        Interfaces\PathTranslator $paths,
        $annotation,
        $pathFormat,
        array &$yaml = null
    ) {
        if (! isset($this->annotations[$annotation])
            || is_null($yaml)
        ) {
            return;
        }
        $path = $this->yamlPathForAnnotation(
            $annotation,
            $pathFormat,
            $paths
        );
        $out = [ $this->fullName() => $yaml ];
        echo "Writing $path\n";
        $paths->createDirectories($path);
        file_put_contents($path, Yaml::dump($out, 20));
    }

    /**
     * Gets yaml file path for an annotation.
     * @param string $annotation Annotation attribute name.
     * @param string $pathFormat Metatdata file path format.
     * @param Interfaces\PathTranslator $paths
     * @return string
     */
    protected function yamlPathForAnnotation(
        $annotation,
        $pathFormat,
        Interfaces\PathTranslator $paths
    ) {
        $base = implode(
            "\\",
            [
                explode("\\", $this->ns())[0],
                $this->annotations[$annotation]->value(),
                self::DUMMY_PATH_ELEMENT
            ]
        );
        return sprintf(
            $pathFormat,
            dirname($paths->pathForName($base)),
            $this->name()
        );
    }
}
