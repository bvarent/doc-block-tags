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
...

Documentation
-------------
...

ToDo
----
* Resolve relative class names in tags to FQCN using /use/ statements in their origin class.
* Use cache from Doctrine Annotation Reader if configured in Doctrine Module?
* Write tests.