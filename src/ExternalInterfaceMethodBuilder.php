<?php
namespace XMITools;

/**
 * Class ExternalInterfaceMethodBuilder
 * Builder for importing an interface method from source file.
 */
class ExternalInterfaceMethodBuilder extends BaseMethodBuilder
{
    const TESTING = true;
    protected $code;
    protected $comment;
    protected $name;
    protected $kind;
    protected $parameters = [];

}
