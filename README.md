PHP DocBlock Tag Reader
=======================
A library to read tags from DocBlocks in PHP class files. Composed of 
phpDocumentor, Doctrine Annotations, and Typo3's ReflectionService.

Goals
-----
* Be performant or at least cachable.
* Support custom tag definitions.
* Be a Zend Framework 2 module.
* Provide a ReflectionService.
 * Create and manage Doctrine ReflectionClasses itself.
 * ReflectionProperty::getType reads @var and class->@property tags.
 * Provide information on a whole collection of classes.
  * Get all subclasses of a class.
  * Get all methods with a certain annotation.
  * etc.
 * Other useful stuff.

Installation
------------
* Add a requirement in your composer.json file.
* Override the default module configuration in your application.config.
  See the module.config for documentation.
* Unfortunately, at the moment you have to add some extra config to your
  composer.json file to have Typo3 installed correctly:
 * If you're using doctrine 2.4.x:  
```javascript
{ "require": {"doctrine/orm": "2.4.x as 2.3"} }
```
 * To install into the `vendor` dir instead of a `Packages` dir:  
```javascript
{
    "extra": {
        "installer-paths": "Typo3 by default installs in /Packages. We want it in /vendor.",
        "installer-paths": {
            "vendor/typo3/Packages/Framework/{$name}/": ["type:typo3-flow-framework"],
            "vendor/typo3/Packages/Application/{$name}/": ["type:typo3-flow-package"],
            "vendor/typo3/Packages/Plugins/{$name}/": ["type:typo3-flow-plugin"],
            "vendor/typo3/Packages/Sites/{$name}/": ["type:typo3-flow-site"],
            "vendor/typo3/Packages/Boilerplates/{$name}/": ["type:typo3-flow-boilerplate"],
            "vendor/typo3/Build/{$name}/": ["type:typo3-flow-build"]
        }
    }
}
```

Usage
-----
```php
// A class we want to read the tags from.
$testClassName = 'DocBlockTags\Tests\Mock\TaggedClass';

// Get your ZF2 servicemanager from somewhere.
$serviceManager = DocBlockTags\Tests\Bootstrap::getServiceManager();

// Get a Doctrine ReflectionClass for our class.
$classFinder = $serviceManager->get('DocBlockTags\ClassFinder');
$reflParser = new Doctrine\Common\Reflection\StaticReflectionParser($testClassName, $classFinder);
$testClassRefl = $reflParser->getReflectionClass();

// Read some tags.
$tagReader = $serviceManager->get('DocBlockTags\TagReader');
$tags = $tagReader->getClassAnnotations($testClassRefl);
```

Documentation
-------------
No more explicit documentation is available at this time.

ToDo
----
* Cache the TagReader
 * Is the TagReader usable by Doctrine\Common\Annotations\CachedReader?
 * Make cache configurable and 
 * Use cache from Doctrine Annotation Reader if configured in Doctrine Module?
* Write tests.
* Make the Reflection Service configurable.
* Make the Reflection Service cachable. (Use Doctrine cache?)
* Make a class to represent a type instead of a string.
* All //TODOs and @todos.
* Get rid of Typo3 dependency.
* More documentation.
 * Custom tag classes.
 * More examples.