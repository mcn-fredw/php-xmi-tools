<?php
namespace XMITools;

use DOMDocument;
use InvalidArgumentException;

/**
 * interface XMIReader
 * Public API for reading a XMI file containing UML elements.
 */
class XMIReader implements Interfaces\XMIReader
{
    const UML_ABSTRACTION_TAG = 'Abstraction';
    const UML_ATTRIBUTE_TAG = 'Attribute';
    const UML_CLASS_TAG = 'Class';
    const UML_DATATYPE_TAG = 'DataType';
    const UML_GENERALIZATION_TAG = 'Generalization';
    const UML_INTERFACE_TAG = 'Interface';
    const UML_METHOD_TAG = 'Operation';
    const UML_PARAMETER_TAG = 'Parameter';
    const UML_TEMPLATEPARAMETER_TAG = 'TemplateParameter';
    const TAB = '    ';

    protected $docV;
    protected $nsV;
    protected $nodeV;

    /**
     * Initialize a new instance.
     */
    protected function __construct($uml, $ns, $node = null)
    {
        $this->docV = $uml;
        $this->nsV = $ns;
        $this->nodeV = $node;
    }

    /**
     * {@inheritDoc}
     */
    public function attributeValue($name)
    {
        return $this->nodeAttributeValue($this->node(), $name);
    }

    /**
     * {@inheritDoc}
     */
    public function child()
    {
        return $this->attributeValue('child');
    }

    /**
     * {@inheritDoc}
     */
    public function client()
    {
        return $this->attributeValue('client');
    }

    /**
     * {@inheritDoc}
     */
    public function code()
    {
        $nodes = $this->docV->getElementsByTagName('sourcecode');
        foreach ($nodes as $child) {
            $id = $this->nodeAttributeValue($child, 'id');
            if ($this->xmiId() == $id) {
                return str_replace(
                    "\t",
                    self::TAB,
                    $this->nodeAttributeValue($child, 'value')
                );
            }
        }
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function comment()
    {
        return str_replace(
            "\t",
            self::TAB,
            $this->attributeValue('comment')
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function createFromFile($ops)
    {
        $path = $ops['xmi-file'];
        $uml = new DOMDocument();
        $uml->load($path);
        $ns = $uml->lookupNamespaceURI('UML');
        return new static($uml, $ns);
    }

    /**
     * {@inheritDoc}
     */
    public function doc()
    {
        return $this->docV;
    }

    /**
     * {@inheritDoc}
     */
    public function fullName()
    {
        return $this->resolveNamespace($this->nodeV);
    }

    /**
     * {@inheritDoc}
     */
    public function initialValue()
    {
        return $this->attributeValue('initialValue');
    }

    /**
     * {@inheritDoc}
     */
    public function isAbstract()
    {
        return filter_var(
            $this->attributeValue('isAbstract'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isStatic()
    {
        return ('classifier' == $this->attributeValue('ownerScope'));
    }

    /**
     * {@inheritDoc}
     */
    public function namespaceOf($name)
    {
        $pos = strrpos($name, '\\');
        if (false === $pos) {
            return '';
        }
        return substr($name, 0, $pos);
    }

    /**
     * {@inheritDoc}
     */
    public function node()
    {
        return $this->nodeV;
    }

    /**
     * {@inheritDoc}
     */
    public function nodeAttributeValue($node, $name)
    {
        if (! isset($node->attributes)) {
            return '';
        }
        $attrib = $node->attributes->getNamedItem($name);
        if (! isset($attrib)) {
            return '';
        }
        return $attrib->value;
    }

    /**
     * {@inheritDoc}
     */
    public function parent()
    {
        return $this->attributeValue('parent');
    }

    /**
     * {@inheritDoc}
     */
    public function resolveNamespace($node)
    {
        $result = '';
        $id = $this->nodeAttributeValue($node, 'namespace');
        if (0 < strlen($id) && false === strpos($id, ' ')) {
            $nodes = $this->docV->getElementsByTagNameNS(
                $this->nsV,
                'Package'
            );
            foreach ($nodes as $parent) {
                if ($this->nodeAttributeValue($parent, 'xmi.id') == $id) {
                    $result = $this->resolveNamespace($parent) .  '\\';
                }
            }
        }
        $result .= $this->nodeAttributeValue($node, 'name');
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function shortName()
    {
        return $this->attributeValue('name');
    }

    /**
     * {@inheritDoc}
     */
    public function stereotype()
    {
        return $this->attributeValue('stereotype');
    }

    /**
     * {@inheritDoc}
     */
    public function supplier()
    {
        return $this->attributeValue('supplier');
    }

    /**
     * {@inheritDoc}
     */
    public function type()
    {
        return $this->attributeValue('type');
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->attributeValue('value');
    }

    /**
     * {@inheritDoc}
     */
    public function visibility()
    {
        return $this->attributeValue('visibility');
    }

    /**
     * {@inheritDoc}
     */
    public function xmiId()
    {
        return $this->attributeValue('xmi.id');
    }

    /**
     * {@inheritDoc}
     */
    public function walkAbstractions($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_ABSTRACTION_TAG,
            $this->docV->getElementsByTagName('XMI.content')[0]
        );
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_GENERALIZATION_TAG,
            $this->docV->getElementsByTagName('XMI.content')[0]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function walkAttributes($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_ATTRIBUTE_TAG,
            $this->nodeV
        );
    }

    /**
     * {@inheritDoc}
     */
    public function walkClasses($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_CLASS_TAG,
            $this->docV->getElementsByTagName('XMI.content')[0]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function walkDatatypes($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_DATATYPE_TAG,
            $this->docV->getElementsByTagName('XMI.content')[0]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function walkElements(
        $callback,
        $passthrough,
        $scanFor,
        $element
    ) {
        if (! is_callable($callback)) {
            throw new InvalidArgumentException(var_export($callback, true));
        }
        $nodes = $element->getElementsByTagNameNS($this->nsV, $scanFor);
        foreach ($nodes as $child) {
            call_user_func(
                $callback,
                new static($this->docV, $this->nsV, $child),
                $passthrough
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function walkInterfaces($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_INTERFACE_TAG,
            $this->docV->getElementsByTagName('XMI.content')[0]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function walkMethods($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_METHOD_TAG,
            $this->nodeV
        );
    }

    /**
     * {@inheritDoc}
     */
    public function walkParameters($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_PARAMETER_TAG,
            $this->nodeV
        );
    }

    /**
     * {@inheritDoc}
     */
    public function walkTemplates($callback, $passthrough)
    {
        $this->walkElements(
            $callback,
            $passthrough,
            static::UML_TEMPLATEPARAMETER_TAG,
            $this->nodeV
        );
    }
}
