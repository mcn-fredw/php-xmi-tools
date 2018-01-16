<?php
namespace XMITools\Interfaces;

/**
 * interface XMIReadable
 * API for object that can be initialized by reading an XMI doc.
 */
interface XMIReadable
{
    /**
     * Initialize object from current XMI document node.
     * @param XMIReader $reader
     * @param ModuleBuilder $builder
     */
    public static function readFromXMI(
        XMIReader $reader,
        ModuleBuilder $builder
    );
}
