# php-xmi-tools

PHP based tool for outputting PHP 7.x code for a Umbrello XMI file.
Tested with Umbrello 2.14.3.

## Installation

Depends on:

symfony/yaml

## Usage

```php src/Main.php --project-dir=directory --xmi-file=xmi-file```

- directory is the path to the base project directory containing your composer.json file.
- xmi-file is the path to your project XMI UML model.

see **bin/test-run** for an example.

## Features

- Tabs in Umbrello documentation/code are converted to 4 spaces.
- Use a Datatype **mixed** to document, but not type hint mixed types.
- Return types that begin with **?** have limited support for nullable return types.
Current code will not try to resolve relative name space imports and hints for Datatypes.
You will need to create a second class or interface to represent custom interfaces and
classes as a nullable return type.
- Use a Datatype **@array** to use as PHP's array type in type hints.
- Declare an attribute/property with a Datatype **@const** and a visibility of **implementation**
to make the attribute/property a constant.
- Declare an attribute/property with a Datatype **@annotation** and a visibility of **implementation**
for special class attributes/properties that won't appear in the source code.
Intended use is to pass information to specialty builders (i.e. DoctrineEntityBuilder).
- Declare an attribute/property with a Datatype **@annotation** and a visibility of **implementation**
for tests (work in progress).
- Templates are treated as name space import hints.
The type is the name space import, the name is the type hint.
- Setting the stereotype of a class or interface to **php-type**
will cause the name space import and type hint to work correctly for an PHP type (i.e. PDO, Exception, ...).
No source code is emitted for classes and interfaces with the stereotype **php-type**.
- Setting the stereotype of a class to **trait** will cause the class's source code to be emitted as a PHP trait.
Attach traits to classes using the Implements generalization (class implements trait).
Attaching an interface to a trait will allow any method code added in Umbrello to be emitted in the trait.
- Code added to an interface method will be emitted in any directly implementing traits or classes.
- Code generator makes an attempt to merge code from pre-existing modules.
- Setting the stereotype of a class to **doctrine-entity**
will cause the source code generator to look for special annotations and attribute/property comments
to generate doctrine YAML files for doctrine entity metadata.
    - An **@annotation** type named **@yaml** has a value of the project
directory relative path to the yaml metadata directory.
The **documentation** for the **@annotation** is where to put general yaml entries for the entity.
    - An **@annotation** type named **@table**
contains the table name in it's value.
    - An **@annotation** type named **@repository**
contains the full class name of the repository in it's value.
    - Properties/attributes in the class, or related traits can specify field yaml by
adding a **@doctrine-yaml** section in the property/attribute documentation (like **@code** sections).
Any text between **@doctrine-yaml** lines is emitted in the fields section of the entity metadata.

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request

## History

- 1.0.1
    - Fix for reading constants and attributes from source.
    - Prevent trait code from being imported in to using classes when reading existing source code.
- 1.0.0
    - Initial version.

## License

MIT License
