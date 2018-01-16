<?php
namespace XMITools\Interfaces;

/**
 * Interface PathTranslator
 * Public API for translating module full name to file system path.
 */
interface PathTranslator
{
    /**
     * Creates directories for a file path.
     * @param string Full file path.
     * @return string Input path.
     */
    public function createDirectories($path);

    /**
     * Translates module full name to file system path.
     * @param string $name Module full name string.
     * @return string File system path for name.
     */
    public function pathForName($name);

    /**
     * Gets project path.
     * @return string.
     */
    public function projectPath();
}
