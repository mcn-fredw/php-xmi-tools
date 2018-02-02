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
- Code generator makes an attempt to merge code from pre-existing modules using PHP reflection.
- Use a Datatype **mixed** to document, but not type hint mixed types.
- Setting a method's return type to an interface or class will case the type
hint to be prefixed with **?**, indicating the method may return null.
For other return types, you will need to specify the **?** in the Datatype name.
- Use a Datatype **@array** to use as PHP's array type in type hints.
- Declare an attribute with a Datatype **@const**
to make the attribute a constant.
- Declare an attribute with a Datatype **@annotation**
for special class attributes that won't appear in the source code.
Intended use is to pass information to specialty builders (i.e. DoctrineEntityBuilder).
- Templates are treated as name space import hints.
The type is the name space import, the name is the type hint.
- Setting the stereotype of a class to **php-type**
will cause the name space import, type hint, and extends to work correctly
for a PHP class type (i.e. Exception).
    - No source code is emitted for classes with the stereotype **php-type**.
- Setting the stereotype of an interface to **php-interface**
will cause the name space import, type hint, implements, and extends to work correctly
for a PHP interface type (i.e. ArrayAccess).
    - No source code is emitted for interfaces with the stereotype **php-interface**.
- Setting the stereotype of a class to **3rd-party-class**
will cause the name space import and type hint to work correctly for a third part class.
    - No source code is emitted for classes with the stereotype **3rd-party-class**.
- Setting the stereotype of a class or interface to **3rd-party-interface**
will cause the name space import, type hint, implements, and extends to work correctly
for an third party interface.
    - No source code is emitted for interfaces with the stereotype **3rd-party-interface**.
- Setting the stereotype of a class to **trait** will cause the class's source code
to be emitted as a PHP trait.
Attach traits to classes using the Implements generalization (class implements trait).
Attaching an interface to a trait will allow any method code added in Umbrello
to be emitted in the trait.
- Declaring at least one attribute of type **@test** to a class or trait will cause
the code generator to create a test class for the class/trait.
    - The test class will be named using the class or trait name with **Test** appended.
    - The test class name space is based on the class or trait name, with **Tests**
    appended to the first component of the class or trait name space.
    - The test class module will include a stub socket class to ```extend``` the class under test,
    or ```use``` the trait under test.
    - Using a name and value of **accessors** will auto generate accessor tests
    for all directly defined attributes in the class or trait.
        - @todo use https://github.com/mcn-fredw/mock-from-yaml-php for test generators.
        - @todo generate accessor tests for indirectly defined attributes.
- Code added to an interface method will be emitted in any directly implementing traits or classes.
- Setting the stereotype of a class to **doctrine-entity**
will cause the source code generator to look for special annotations and attribute comments
to generate doctrine YAML files for doctrine entity metadata.
    - An **@annotation** type named **@yaml** has a value of the project
directory relative path to the yaml metadata directory.
The **documentation** for the **@annotation** is where to put general yaml entries for the entity.
    - An **@annotation** type named **@table**
contains the table name in it's value.
    - An **@annotation** type named **@repository**
contains the class name of the repository in it's value.
A bare class name is assumed to be in the same name space as the entity class.
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

- See release notes or latter revisions.
- 1.0.1
    - Fix for reading constants and attributes from source.
    - Prevent trait code from being imported in to using classes when reading existing source code.
- 1.0.0
    - Initial version.

## License

MIT License
