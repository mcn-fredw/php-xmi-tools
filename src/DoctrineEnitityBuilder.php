<?php
namespace XMITools;

class DoctrineEnitityBuilder extends ClassBuilder
{
    const DIR_FORMAT = "%s"
        . DIRECTORY_SEPARATOR
        . "%s"
        . DIRECTORY_SEPARATOR
        . "%s.dcm.yml";

    /**
     * Appends @doctring-yaml section of attribute comments
     * to lines array.
     * @param array &$base Reference to base yaml lines for entity.
     * @param array &$fields Reference to fields list yaml lines.
     * @param array &$src Reference to attributes list.
     * @param Interfaces\ModuleStore $store For module lookup.
     */
    protected function appendAttributeFields(
        array &$base,
        array &$fields,
        array &$src,
        Interfaces\ModuleStore $store
    ) {
        $scanStr = str_repeat(self::TAB, 3);
        foreach ($src as $attrib) {
            $cmt = explode("\n", $attrib->comment());
            $keep = false;
            $named = false;
            $fiedDef = [];
            while (count($cmt)) {
                $line = array_shift($cmt);
                if (false !== strpos($line, '@doctrine-yaml')) {
                    $keep ^= true;
                } elseif ($keep) {
                    if (! $named) {
                        $fiedDef[] = sprintf(
                            "%s%s:",
                            str_repeat(self::TAB, 2),
                            $attrib->name()
                        );
                        $named = true;
                    }
                    $fiedDef[] = sprintf(
                        "%s%s",
                        str_repeat(self::TAB, 3),
                        $line
                    );
                }
            }
            $fieldsLen = count($fiedDef);
            if (0 == $fieldsLen) {
                continue;
            }
            /* append to fields or replace base */
            $n = count($base);
            $i = 0;
            while ($i < $n) {
                if ($fiedDef[0] == $base[$i]) {
                    /* found field def in id: section */
                    break;
                }
                $i += 1;
            }
            if ($i == $n) {
                /* field def not in id: section */
                array_splice(
                    $fields,
                    $fieldsLen,
                    0,
                    $fiedDef
                );
                continue;
            }
            $j = $i + 1;
            while ($j < $n) {
                if (false === strpos($base[$j], $scanStr)) {
                    /* found next field def in base */
                    break;
                }
                $j += 1;
            }
            /* replace field def in base */
            array_splice(
                $base,
                $i,
                $j - $i,
                $fiedDef
            );
        }
    }

    /**
     * Appends @doctring-yaml section of trait attribute comments
     * to lines array.
     * @param array &$base Reference to base yaml lines for entity.
     * @param array &$fields Reference to fields list yaml lines.
     * @param Interfaces\ModuleStore $store For module lookup.
     */
    protected function appendTraitFields(
        array &$base,
        array &$fields,
        Interfaces\ModuleStore $store
    ) {
        foreach ($this->traits as $tKey => $tHint) {
            if ($tKey == $tHint) {
                continue;
            }
            $module = $store->getModule($tKey);
            $this->appendAttributeFields(
                $base,
                $fields,
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
            $repo = sprintf("%s\\%s", $this->ns, $repo);
        }
        $lines = [
            sprintf('%s:', $this->fullName()),
            sprintf('%stype: entity', self::TAB),
            sprintf('%stable: %s', self::TAB, $table),
            sprintf('%srepositoryClass: %s', self::TAB, $repo)
        ];
        $yaml = explode("\n", $this->annotations['@yaml']->comment());
        foreach ($yaml as $line) {
            $lines[] = sprintf('%s%s', self::TAB, $line);
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
        $yamlPath = sprintf(
            self::DIR_FORMAT,
            $paths->projectPath(),
            $this->annotations['@yaml']->value(),
            str_replace('\\', '.', $fullName)
        );
        echo "Writing entity yaml $yamlPath\n";
        $base = $this->beginDoctrineYaml();
        $fields = [ sprintf('%sfields:', self::TAB) ];
        $this->appendAttributeFields(
            $base,
            $fields,
            $this->attributes,
            $store
        );
        $this->appendTraitFields($base, $fields, $store);
        $paths->createDirectories($yamlPath);
        $fd = fopen($yamlPath, 'w');
        foreach ($base as $line) {
            if (is_array($line)) {
                var_dump($line);
                throw new UnexpectedValueException("writing file $yamlPath");
            }
            fwrite($fd, "$line\n");
        }
        foreach ($fields as $line) {
            if (is_array($line)) {
                var_dump($line);
                throw new UnexpectedValueException("writing file $yamlPath");
            }
            fwrite($fd, "$line\n");
        }
        fclose($fd);
    }
}
