<?php

namespace DocBlockTags\Reflection;

/**
 * Contract for a reflection service.
 * Based on TYPO3\Flow\Reflection\ReflectionService
 * 
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
interface ReflectionServiceInterface
{

    /**
     * Builds the reflection data cache during compile time.
     *
     * This method is called by the CompiletimeObjectManager which also determines
     * the list of classes to consider for reflection.
     *
     * @param array $availableClassNames List of all class names to consider for reflection
     * @return void
     */
    public function buildReflectionData(array $availableClassNames);

    /**
     * Tells if the specified class is known to this reflection service and
     * reflection information is available.
     *
     * @param string $className Name of the class
     * @return boolean If the class is reflected by this service
     * @api
     */
    public function isClassReflected($className);

    /**
     * Returns the names of all classes known to this reflection service.
     *
     * @return array Class names
     * @api
     */
    public function getAllClassNames();

    /**
     * Searches for and returns the class name of the default implementation of the given
     * interface name. If no class implementing the interface was found or more than one
     * implementation was found in the package defining the interface, FALSE is returned.
     *
     * @param string $interfaceName Name of the interface
     * @return mixed Either the class name of the default implementation for the object type or FALSE
     * @throws \InvalidArgumentException if the given interface does not exist
     * @api
     */
    public function getDefaultImplementationClassNameForInterface($interfaceName);

    /**
     * Searches for and returns all class names of implementations of the given object type
     * (interface name). If no class implementing the interface was found, an empty array is returned.
     *
     * @param string $interfaceName Name of the interface
     * @return array An array of class names of the default implementation for the object type
     * @throws \InvalidArgumentException if the given interface does not exist
     * @api
     */
    public function getAllImplementationClassNamesForInterface($interfaceName);

    /**
     * Searches for and returns all names of classes inheriting the specified class.
     * If no class inheriting the given class was found, an empty array is returned.
     *
     * @param string $className Name of the parent class
     * @return array An array of names of those classes being a direct or indirect subclass of the specified class
     * @throws \InvalidArgumentException if the given class does not exist
     * @api
     */
    public function getAllSubClassNamesForClass($className);

    /**
     * Returns the class name of the given object. This is a convenience
     * method that returns the expected class names even for proxy classes.
     *
     * @param object $object
     * @return string The class name of the given object
     */
    public function getClassNameByObject($object);

    /**
     * Searches for and returns all names of classes which are tagged by the specified
     * annotation. If no classes were found, an empty array is returned.
     *
     * @param string $annotationClassName Name of the annotation class, for example "TYPO3\Flow\Annotations\Aspect"
     * @return array
     */
    public function getClassNamesByAnnotation($annotationClassName);

    /**
     * Tells if the specified class has the given annotation
     *
     * @param string $className Name of the class
     * @param string $annotationClassName Annotation to check for
     * @return boolean
     * @api
     */
    public function isClassAnnotatedWith($className, $annotationClassName);

    /**
     * Returns the specified class annotations or an empty array
     *
     * @param string $className Name of the class
     * @param string $annotationClassName Annotation to filter for
     * @return array<object>
     */
    public function getClassAnnotations($className, $annotationClassName = NULL);

    /**
     * Returns the specified class annotation or NULL.
     *
     * If multiple annotations are set on the target you will
     * get one (random) instance of them.
     *
     * @param string $className Name of the class
     * @param string $annotationClassName Annotation to filter for
     * @return object
     */
    public function getClassAnnotation($className, $annotationClassName);

    /**
     * Tells if the specified class is abstract or not
     *
     * @param string $className Name of the class to analyze
     * @return boolean TRUE if the class is abstract, otherwise FALSE
     * @api
     */
    public function isClassAbstract($className);

    /**
     * Tells if the class is unconfigurable or not
     *
     * @param string $className Name of the class to analyze
     * @return boolean return TRUE if class not could not be automatically configured, otherwise FALSE
     * @api
     */
    public function isClassUnconfigurable($className);

    /**
     * Returns all class names of classes containing at least one method annotated
     * with the given annotation class
     *
     * @param string $annotationClassName The annotation class name for a method annotation
     * @return array An array of class names
     */
    public function getClassesContainingMethodsAnnotatedWith($annotationClassName);

    /**
     * Tells if the specified method is final or not
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to analyze
     * @return boolean TRUE if the method is final, otherwise FALSE
     * @api
     */
    public function isMethodFinal($className, $methodName);

    /**
     * Tells if the specified method is declared as static or not
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to analyze
     * @return boolean TRUE if the method is static, otherwise FALSE
     * @api
     */
    public function isMethodStatic($className, $methodName);

    /**
     * Tells if the specified method is public
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to analyze
     * @return boolean TRUE if the method is public, otherwise FALSE
     * @api
     */
    public function isMethodPublic($className, $methodName);

    /**
     * Tells if the specified method is protected
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to analyze
     * @return boolean TRUE if the method is protected, otherwise FALSE
     * @api
     */
    public function isMethodProtected($className, $methodName);

    /**
     * Tells if the specified method is private
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to analyze
     * @return boolean TRUE if the method is private, otherwise FALSE
     * @api
     */
    public function isMethodPrivate($className, $methodName);

    /**
     * Tells if the specified method is tagged with the given tag
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to analyze
     * @param string $tag Tag to check for
     * @return boolean TRUE if the method is tagged with $tag, otherwise FALSE
     * @api
     */
    public function isMethodTaggedWith($className, $methodName, $tag);

    /**
     * Tells if the specified method has the given annotation
     *
     * @param string $className Name of the class
     * @param string $methodName Name of the method
     * @param string $annotationClassName Annotation to check for
     * @return boolean
     * @api
     */
    public function isMethodAnnotatedWith($className, $methodName, $annotationClassName);

    /**
     * Returns the specified method annotations or an empty array
     *
     * @param string $className Name of the class
     * @param string $methodName Name of the method
     * @param string $annotationClassName Annotation to filter for
     * @return array<object>
     * @api
     */
    public function getMethodAnnotations($className, $methodName, $annotationClassName = NULL);

    /**
     * Returns the specified method annotation or NULL.
     *
     * If multiple annotations are set on the target you will
     * get one (random) instance of them.
     *
     * @param string $className Name of the class
     * @param string $methodName Name of the method
     * @param string $annotationClassName Annotation to filter for
     * @return object
     */
    public function getMethodAnnotation($className, $methodName, $annotationClassName);

    /**
     * Returns the names of all properties of the specified class
     *
     * @param string $className Name of the class to return the property names of
     * @return array An array of property names or an empty array if none exist
     * @api
     */
    public function getClassPropertyNames($className);

    /**
     * Wrapper for method_exists() which tells if the given method exists.
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method
     * @return boolean
     * @api
     */
    public function hasMethod($className, $methodName);

    /**
     * Returns all tags and their values the specified method is tagged with
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to return the tags and values of
     * @return array An array of tags and their values or an empty array of no tags were found
     * @api
     */
    public function getMethodTagsValues($className, $methodName);

    /**
     * Returns an array of parameters of the given method. Each entry contains
     * additional information about the parameter position, type hint etc.
     *
     * @param string $className Name of the class containing the method
     * @param string $methodName Name of the method to return parameter information of
     * @return array An array of parameter names and additional information or an empty array of no parameters were found
     * @api
     */
    public function getMethodParameters($className, $methodName);

    /**
     * Searches for and returns all names of class properties which are tagged by the specified tag.
     * If no properties were found, an empty array is returned.
     *
     * @param string $className Name of the class containing the properties
     * @param string $tag Tag to search for
     * @return array An array of property names tagged by the tag
     * @api
     */
    public function getPropertyNamesByTag($className, $tag);

    /**
     * Returns all tags and their values the specified class property is tagged with
     *
     * @param string $className Name of the class containing the property
     * @param string $propertyName Name of the property to return the tags and values of
     * @return array An array of tags and their values or an empty array of no tags were found
     * @api
     */
    public function getPropertyTagsValues($className, $propertyName);

    /**
     * Returns the values of the specified class property tag
     *
     * @param string $className Name of the class containing the property
     * @param string $propertyName Name of the tagged property
     * @param string $tag Tag to return the values of
     * @return array An array of values or an empty array if the tag was not found
     * @api
     */
    public function getPropertyTagValues($className, $propertyName, $tag);

    /**
     * Tells if the specified property is private
     *
     * @param string $className Name of the class containing the method
     * @param string $propertyName Name of the property to analyze
     * @return boolean TRUE if the property is private, otherwise FALSE
     * @api
     */
    public function isPropertyPrivate($className, $propertyName);

    /**
     * Tells if the specified property is public
     *
     * @param string $className Name of the class containing the method
     * @param string $propertyName Name of the property to analyze
     * @return boolean TRUE if the property is public, otherwise FALSE
     */
    public function isPropertyPublic($className, $propertyName);
    
    /**
     * Tells if the specified property is static
     *
     * @param string $className Name of the class containing the method
     * @param string $propertyName Name of the property to analyze
     * @return boolean TRUE if the property is static, otherwise FALSE
     */
    public function isPropertyStatic($className, $propertyName);
    
    /**
     * Tells if the specified property is readable
     *
     * @param string $className Name of the class containing the method
     * @param string $propertyName Name of the property to analyze
     * @return boolean TRUE if the property is readable, otherwise FALSE
     */
    public function isPropertyReadable($className, $propertyName);
    
    /**
     * Tells if the specified property is writable
     *
     * @param string $className Name of the class containing the method
     * @param string $propertyName Name of the property to analyze
     * @return boolean TRUE if the property is writable, otherwise FALSE
     */
    public function isPropertyWritable($className, $propertyName);

    /**
     * Tells if the specified class property is tagged with the given tag
     *
     * @param string $className Name of the class
     * @param string $propertyName Name of the property
     * @param string $tag Tag to check for
     * @return boolean TRUE if the class property is tagged with $tag, otherwise FALSE
     * @api
     */
    public function isPropertyTaggedWith($className, $propertyName, $tag);

    /**
     * Tells if the specified property has the given annotation
     *
     * @param string $className Name of the class
     * @param string $propertyName Name of the method
     * @param string $annotationClassName Annotation to check for
     * @return boolean
     * @api
     */
    public function isPropertyAnnotatedWith($className, $propertyName, $annotationClassName);

    /**
     * Searches for and returns all names of class properties which are marked by the
     * specified annotation. If no properties were found, an empty array is returned.
     *
     * @param string $className Name of the class containing the properties
     * @param string $annotationClassName Class name of the annotation to search for
     * @return array An array of property names carrying the annotation
     * @api
     */
    public function getPropertyNamesByAnnotation($className, $annotationClassName);

    /**
     * Returns the specified property annotations or an empty array
     *
     * @param string $className Name of the class
     * @param string $propertyName Name of the property
     * @param string $annotationClassName Annotation to filter for
     * @return array<object>
     * @api
     */
    public function getPropertyAnnotations($className, $propertyName, $annotationClassName = NULL);

    /**
     * Returns the specified property annotation or NULL.
     *
     * If multiple annotations are set on the target you will
     * get one (random) instance of them.
     *
     * @param string $className Name of the class
     * @param string $propertyName Name of the property
     * @param string $annotationClassName Annotation to filter for
     * @return object
     */
    public function getPropertyAnnotation($className, $propertyName, $annotationClassName);
    
    /**
     * Returns the type (class name or primitive) of the property.
     *
     * @param string $className Name of the class
     * @param string $propertyName Name of the property
     * @return string
     */
    public function getPropertyType($className, $propertyName);

    /**
     * Returns the class schema for the given class
     *
     * @param mixed $classNameOrObject The class name or an object
     * @return \TYPO3\Flow\Reflection\ClassSchema
     * @todo Interface \TYPO3\Flow\Reflection\ClassSchema
     */
    public function getClassSchema($classNameOrObject);
}
