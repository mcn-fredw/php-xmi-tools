<?php
namespace XMITools;

class DoctrineEnitityBuilder extends ClassBuilder
{
    const DIR_FORMAT = "%s"
        . DIRECTORY_SEPARATOR
        . "%s.dcm.yml";
    /* Yaml orm mapping parts. */
    protected $yaml = [
        'base' => [],
        'id' => [],
        'fields' => [],
        'other' => []
    ];

    /**
     * Appends @doctring-yaml section of attribute comments
     * to lines array.
     * @param array &$src Reference to attributes list.
     * @param Interfaces\ModuleStore $store For module lookup.
     */
    protected function appendAttributeFields(
        array &$src,
        Interfaces\ModuleStore $store
    ) {
        $scanStr = str_repeat(self::TAB, 3);
        foreach ($src as $attrib) {
            $cmt = explode("\n", $attrib->comment());
            $keep = false;
            $field = [];
            foreach ($cmt as $line) {
                if (false !== strpos($line, '@doctrine-yaml')) {
                    $keep ^= true;
                    if ($keep) {
                        $field[] = sprintf(
                            "%s%s:",
                            str_repeat(self::TAB, 2),
                            $attrib->name()
                        );
                    }
                    continue;
                }
                if ($keep) {
                    $field[] = sprintf(
                        "%s%s",
                        str_repeat(self::TAB, 3),
                        $line
                    );
                }
            }
            $fieldsLen = count($field);
            if (2 > $fieldsLen) {
                continue;
            }
            /* append to fields or replace base */
            $n = count($this->yaml['base']);
            $i = 0;
            while ($i < $n) {
                if ($field[0] == $this->yaml['base'][$i]) {
                    /* found field def in id: section */
                    break;
                }
                $i += 1;
            }
            if ($i == $n) {
                /* field def not in id: section */
                array_splice(
                    $this->yaml['fields'],
                    count($this->yaml['fields']),
                    0,
                    $field
                );
                continue;
            }
            $j = $i + 1;
            while ($j < $n) {
                if (false === strpos($this->yaml['base'][$j], $scanStr)) {
                    /* found next field def in base */
                    break;
                }
                $j += 1;
            }
            /* replace field def in base */
            array_splice(
                $this->yaml['base'],
                $i,
                $j - $i,
                $field
            );
        }
    }

    /**
     * Appends @doctring-yaml section of trait attribute comments
     * to lines array.
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
     * Builds start of yaml file content.
     * @return array File lines.
     */
    protected function beginDoctrineYaml()
    {
        $table = $this->annotations['@table']->value();
        $repo = $this->annotations['@repository']->value();
        if (false === strpos($repo, "\\")) {
            $repo = implode(
                "\\",
                [
                    explode("\\", $this->ns)[0],
                    'Repository',
                    $repo
                ]
            );
        }
        $this->yaml['base'] = [
            sprintf('%s:', $this->fullName()),
            sprintf('%stype: entity', self::TAB),
            sprintf('%stable: %s', self::TAB, $table),
            sprintf('%srepositoryClass: %s', self::TAB, $repo)
        ];
        $part = 'other';
        $yaml = explode("\n", $this->annotations['@yaml']->comment());
        foreach ($yaml as $line) {
            if (0 == strlen($line)) {
                continue;
            }
            if (ord(' ') != ord($line)) {
                $part = 'other';
                if ('id:' === substr($line, 0, 3)) {
                    $part = 'id';
                }
            }
            $this->yaml[$part][] = sprintf('%s%s', self::TAB, $line);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function writeModule(
        Interfaces\ModuleStore $store,
        Interfaces\PathTranslator $paths
    ) {
        parent::writeModule($store, $paths);
        $this->writeYaml($store, $paths);
    }

    /**
     * Builds and writes yaml metadata for entity.
     * @param Interfaces\ModuleStore $store
     * @param Interfaces\PathTranslator $paths
     */
    protected function writeYaml(
        Interfaces\ModuleStore $store,
        Interfaces\PathTranslator $paths
    ) {
        /* calc yaml file path */
        $fullName = $this->fullName();
        $ormBase = implode(
            "\\",
            [
                explode("\\", $this->ns())[0],
                $this->annotations['@yaml']->value(),
                'dummy'
            ]
        );
        $yamlPath = sprintf(
            self::DIR_FORMAT,
            dirname($paths->pathForName($ormBase)),
            str_replace('\\', '.', $fullName)
        );
        echo "Writing entity yaml $yamlPath\n";
        $this->beginDoctrineYaml();
        $this->yaml['fields'] = [
            sprintf('%sfields:', self::TAB)
        ];
        $this->appendAttributeFields(
            $this->attributes,
            $store
        );
        $this->appendTraitFields($store);
        $paths->createDirectories($yamlPath);
        $fd = fopen($yamlPath, 'w');
        foreach ($this->yaml as $lines) {
            foreach ($lines as $line) {
                if (is_array($line)) {
                    var_dump($line);
                    throw new UnexpectedValueException(
                        "writing file $yamlPath"
                    );
                }
                fwrite($fd, "$line\n");
            }
        }
        fclose($fd);
        $this->yaml = [];
    }
}
