<?php
namespace XMITools\Interfaces;

/**
 * Interface XMIReader
 * Public API for reading a XMI file containing UML elements.
 */
interface XMIReader
{
    /**
     * Gets attribute value for current node.
     * @param string $name Attribute name string.
     * @return string Attribute value.
     * Empty string if attribute does not exist.
     */
    public function attributeValue($name);

    /**
     * Gets child attribute value for current node.
     * @return string
     */
    public function child();

    /**
     * Gets client attribute value for current node.
     * @return string
     */
    public function client();

    /**
     * Gets source code for current method node.
     * @return string
     */
    public function code();

    /**
     * Gets comment attribute value for current node.
     * @return string
     */
    public function comment();

    /**
     * Reads a UML file.
     * @param array $argv Array of command line arguments.
     * Assumes first element is XMI file path.
     * @return XMIReader
     */
    public static function createFromFile($argv);

    /**
     * Gets the DOM document object for the UML file.
     * @return DOMDocument
     */
    public function doc();

    /**
     * Gets full qualified module name for current node.
     * @return string
     */
    public function fullName();

    /**
     * Gets initialValue attribute value for current node.
     * @return string
     */
    public function initialValue();

    /**
     * Gets flag for the current element is abstract.
     * @return bool
     */
    public function isAbstract();

    /**
     * Gets flag for the current node is a static element.
     * @return bool
     */
    public function isStatic();

    /**
     * Gets the namespace portion from a module's full name.
     * @param string $name
     * @return string
     */
    public function namespaceOf($name);

    /**
     * Gets the DOM node the reader is looking at.
     * @return DOMNode
     */
    public function node();

    /**
     * Gets attribute value for specific node.
     * @param DOMNode $node.
     * @param string $name Attribute name string.
     * @return string Attribute value.
     * Empty string if attribute does not exist.
     */
    public function nodeAttributeValue($node, $name);

    /**
     * Gets parent attribute value for current node.
     * @return string
     */
    public function parent();

    /**
     * Gets namespace name for a node.
     * @param DOMNode $node
     * @return string
     */
    public function resolveNamespace($node);

    /**
     * Gets stereotype attribute value for current node.
     * @return string
     */
    public function shortName();

    /**
     * Gets name attribute value for current node.
     * @return string
     */
    public function stereotype();

    /**
     * Gets supplier attribute value for current node.
     * @return string
     */
    public function supplier();

    /**
     * Gets type attribute value for current node.
     * @return string
     */
    public function type();

    /**
     * Gets value attribute value for current node.
     * @return string
     */
    public function value();

    /**
     * Gets type attribute value for current node.
     * @return string
     */
    public function visibility();

    /**
     * Calls callback for each abstraction in the XMI doc.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkAbstractions($callback, $passthrough);

    /**
     * Calls callback for each class attribute in the current node.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkAttributes($callback, $passthrough);

    /**
     * Calls callback for each class in the XMI doc.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkClasses($callback, $passthrough);

    /**
     * Calls callback for each datatype in the XMI doc.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkDatatypes($callback, $passthrough);

    /**
     * Calls callback for each scanFor tag in XMI doc.
     * @param callable $callback
     * @param mixed $passthhrough
     * @param string $scanFor XMT tag to scan for.
     * @param DOMEntity $element DOM container to scan.
     */
    public function walkElements(
        $callback,
        $passthrough,
        $scanFor,
        $element
    );

    /**
     * Calls callback for each interface in XMI doc.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkInterfaces($callback, $passthrough);

    /**
     * Calls callback for each method in current node.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkMethods($callback, $passthrough);

    /**
     * Calls callback for each method parameter in current node.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkParameters($callback, $passthrough);

    /**
     * Calls callback for each template parameter in current node.
     * @param callable $callback
     * @param mixed $passthhrough
     */
    public function walkTemplates($callback, $passthrough);

    /**
     * Gets xmi.id attribute value for current node.
     * @return string
     */
    public function xmiId();
}
