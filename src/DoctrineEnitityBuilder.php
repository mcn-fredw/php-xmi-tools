<?php
namespace XMITools;

class DoctrineEnitityBuilder extends ClassBuilder
{
    const DIR_FORMAT = "%s"
        . DIRECTORY_SEPARATOR
        . "%s"
        . DIRECTORY_SEPARATOR
        . "%s.dcm.yaml";

    /**
     */
    protected function appendAttributeFields(
        array &$lines,
        array &$src,
        Interfaces\ModuleStore $store
    ) {
        foreach ($src as $attrib) {
            $cmt = explode("\n", $attrib->comment());
            $keep = false;
            $named = false;
            while (count($cmt)) {
                $line = array_shift($cmt);
                if (false !== strpos($line, '@doctrine-yaml')) {
                    $keep ^= true;
                } elseif ($keep) {
                    if (! $named) {
                        $lines[] = sprintf(
                            "%s%s:",
                            str_repeat(self::TAB, 2),
                            $attrib->name()
                        );
                        $named = true;
                    }
                    $lines[] = sprintf(
                        "%s%s",
                        str_repeat(self::TAB, 3),
                        $line
                    );
                }
            }
        }
    }

    /**
     */
    protected function appendTraitFields(
        array &$lines,
        Interfaces\ModuleStore $store
    ) {
        foreach ($this->traits as $tKey => $tHint) {
            if ($tKey == $tHint) {
                continue;
            }
            $module = $store->getModule($tKey);
            $this->appendAttributeFields(
                $lines,
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
        $lines[] = sprintf('%sfields:', self::TAB);
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
        $lines = $this->beginDoctrineYaml();
        $this->appendAttributeFields($lines, $this->attributes, $store);
        $this->appendTraitFields($lines, $store);
        $paths->createDirectories($yamlPath);
        $fd = fopen($yamlPath, 'w');
        foreach ($lines as $line) {
            if (is_array($line)) {
                var_dump($line);
                throw new UnexpectedValueException("writing file $yamlPath");
            }
            fwrite($fd, "$line\n");
        }
        fclose($fd);
    }
}
