PHP DocBlock Tag Reader
=======================
A library to read tags from DocBlocks in PHP class files. A bit similar to 
Doctrine Annotations.

Goals
-----
* Be performant or at least cachable.
* Support custom tag definitions.
* Be a Zend Framework 2 module.

Installation
------------
Add a requirement to your composer.json file.

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
* Cache
 * Is the TagReader usable by Doctrine\Common\Annotations\CachedReader?
 * Make cache configurable and 
 * Use cache from Doctrine Annotation Reader if configured in Doctrine Module?
* Perhaps create a reflection service.
 * Create and manage Doctrine ReflectionClasses itself.
 * ReflectionProperty::getType reads @var and class->@property tags.
 * Other useful stuff.
* Write tests.
* More documentation.
 * Custom tag classes.
 * More examples.