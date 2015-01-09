<?php

namespace DocBlockTags\Reflection;

use DocBlockTags\ServiceManager\TagReaderAwareInterface;
use DocBlockTags\TagReader;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Reflection\StaticReflectionClass;
use Doctrine\Common\Reflection\StaticReflectionParser;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Tag;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;
use TYPO3\Flow\Reflection\ClassReflection;
use TYPO3\Flow\Reflection\ReflectionService as Typo3ReflectionService;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Implementation of a ReflectionService, using mostly Typo3's ReflectionService.
 * {@see Typo3ReflectionService}
 * With the addition of gathering information from PhpDoc tags.
 * I.e. property/method types and 'magic' properties/methods.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class ReflectionService extends Typo3ReflectionService implements
ReflectionServiceInterface, ServiceManagerAwareInterface
{
    
    const   DATA_PROPERTY_TYPE = 91,
            DATA_PROPERTY_ACCESSIBILITY = 92,
            ACCESSIBILITY_READ = 1,
            ACCESSIBILITY_WRITE = 2,
            DATA_METHOD_ANNOTATIONS = 93,
            DATA_METHOD_TYPE = 94,
            DATA_PROPERTY_STATIC = 95;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * As {@see parent}, but does not care if the class is completely unknown.
     */
    protected function loadOrReflectClassIfNecessary($className)
    {
        if (isset($this->classReflectionData[$className])
                && is_array($this->classReflectionData[$className])
        ) {
            return;
        }

        if ($this->loadFromClassSchemaRuntimeCache === TRUE) {
            $this->classReflectionData[$className] = $this->reflectionDataRuntimeCache->get(str_replace('\\', '_', $className));
        } else {
            $this->reflectClass($className);
        }
    }

    /**
     * {@see addTagsAsAnnotations} for a class.
     * @param StaticReflectionParser $reflectionParser
     */
    protected function addClassTagsAsAnnotations(StaticReflectionParser $reflectionParser)
    {
        $className = $reflectionParser->getClassName();
        $classReflector = $reflectionParser->getReflectionClass();
        $this->addTagsAsAnnotations($classReflector, $this->classReflectionData[$className]);
    }
    
    /**
     * Determines if the property is static and saves it in the classReflectionData.
     * @param string $className
     * @param string $propertyName
     * @return boolean The result.
     */
    protected function addPropertyStaticInfo($className, $propertyName)
    {
        $static = $this->digArray($this->classReflectionData,
                $className,
                static::DATA_CLASS_PROPERTIES,
                $propertyName,
                static::DATA_PROPERTY_STATIC);
        if (null === $static) {
            // Unfortunately we have no access to the original ReflectionProperty
            //  so we have to create a new one.
            $propRefl = new ReflectionProperty($className, $propertyName);
            $static = $propRefl->isStatic();
        }
        $this->burryArray($this->classReflectionData, $static,
                $className,
                static::DATA_CLASS_PROPERTIES,
                $propertyName,
                static::DATA_PROPERTY_STATIC);
        
        return $static;
    }

    /**
     * {@see addTagsAsAnnotations} for a class' properties.
     * @param StaticReflectionParser $reflectionParser
     * @todo Since StaticReflectionProperty does not support docblocks for static
     *  members, tags are not added for those atm.
     */
    protected function addPropertyTagsAsAnnotations(StaticReflectionParser $reflectionParser)
    {
        $className = $reflectionParser->getClassName();
        $properties =& $this->classReflectionData[$className][static::DATA_CLASS_PROPERTIES];
        if (!is_array($properties)) {
            return;
        }
        foreach ($properties as $propertyName => &$property) {
            $reflector = $reflectionParser->getReflectionProperty($propertyName);
            $isStatic = $this->addPropertyStaticInfo($className, $propertyName);
            if (!$isStatic) {
                $this->addTagsAsAnnotations($reflector, $property);
            }
        }
    }

    /**
     * {@see addTagsAsAnnotations} for a class' methods.
     * @param StaticReflectionParser $reflectionParser
     * @todo Since StaticReflectionProperty does not support docblocks for static
     *  members, tags are not added for those atm.
     */
    protected function addMethodTagsAsAnnotations(StaticReflectionParser $reflectionParser)
    {
        $className = $reflectionParser->getClassName();
        $methods =& $this->classReflectionData[$className][static::DATA_CLASS_METHODS];
        if (!is_array($methods)) {
            return;
        }
        foreach ($methods as $methodName => &$method) {
            $reflector = $reflectionParser->getReflectionMethod($methodName);
            $isStatic = $this->digArray($method, static::DATA_METHOD_STATIC);
            if (!$isStatic) {
                $this->addTagsAsAnnotations($reflector, $method);
            }
        }
    }
    
    /**
     * Extracts the Tags from the DocBlock of a target (class, property or node),
     *  if they were not already recognized as Annotations. And adds those to the
     *  classReflectionData as if they were Annotations.
     * @param Reflector $reflector
     * @internal {@see reflectClass} is supposed to have already run.
     */
    protected function addTagsAsAnnotations(Reflector $reflector, array & $targetNode)
    {
        $tagReader = $this->serviceManager->get('DocBlockTags\TagReader');
        /* @var $tagReader TagReader */
        
        // Find out target (class, property or method).
        $targetType = $tagReader->getTarget($reflector);
        switch ($targetType) {
            case (Target::TARGET_CLASS):
                $annotationsKey = static::DATA_CLASS_ANNOTATIONS;
                $tags = $tagReader->getClassAnnotations($reflector);
                break;
            case (Target::TARGET_PROPERTY):
                $annotationsKey = static::DATA_PROPERTY_ANNOTATIONS;
                $tags = $tagReader->getPropertyAnnotations($reflector);
                break;
            case (Target::TARGET_METHOD):
                $annotationsKey = static::DATA_METHOD_ANNOTATIONS;
                $tags = $tagReader->getMethodAnnotations($reflector);
                break;
            default:
                throw new InvalidArgumentException('The given Reflector is neither '
                        . 'a class, property or method reflector.');
        }
        
        // Stop if there are no tags anyway.
        if (!count($tags)) {
            return;
        }
        
        // Create index of the names of already extracted annotations.
        $knownAnnotations = (array) $this->digArray($targetNode,
                $annotationsKey);
        $knownAnnotationNames = array();
        foreach ($knownAnnotations as $knownAnnotationSet) {
            if (!is_array($knownAnnotationSet)) {
                $knownAnnotationSet = array($knownAnnotationSet);
            }
            foreach ($knownAnnotationSet as $knownAnnotation) {
                $knownAnnotationName = get_class($knownAnnotation);
                $knownAnnotationNames[$knownAnnotationName] = true;
                // TODO Instead of the full class name, get the contexted tag name. E.g. @ORM\Id vs @Id. (This might be impossible.)
            }
        }
        
        foreach ($tags as $tagAnnotation) {
            /* @var $tagAnnotation Tag */
            // Skip tags that were already parsed as a real Annotation.
            // TODO Account for mixed FQCNames and alias names. E.g. @ORM\Id vs @Id. Atm, the last part of the class name is compared.
            foreach (array_keys($knownAnnotationNames) as $knownAnnotationName) {
                if (static::stringEndsWith($knownAnnotationName, $tagAnnotation->getName(), true)) {
                    continue;
                }
            }
            
            // Save the annotation.
            if ($targetType == Target::TARGET_CLASS) {
                /* @var $reflector \ReflectionClass */
                $className = $reflector->getName();
                $annotationClassName = get_class($tagAnnotation);
                $this->annotatedClasses[$annotationClassName][$className] = TRUE;
            }
            $targetNode[$annotationsKey][] = $tagAnnotation;
            // TODO Perhaps also unset $targetNode[static::DATA_xx_TAGS_VALUES[$tagAnnotation->getName()] .
        }
    }
    
    /**
     * Extracts info about properties from the properties' own tags and the
     *  class' @property tags. Info = existence, type and readability.
     * @internal {@see addClassTagsAsAnnotations} is supposed to have already run
     *  for the class. As is {@see addPropertyTagsAsAnnotations}.
     */
    protected function extractPropertyInfoFromTags($className)
    {
        // Get a pointer to the properties array.
        if (!is_array($this->digArray($this->classReflectionData, $className, static::DATA_CLASS_PROPERTIES))) {
            $this->classReflectionData[$className][static::DATA_CLASS_PROPERTIES] = array();
        }
        $properties =& $this->classReflectionData[$className][static::DATA_CLASS_PROPERTIES];
        
        // Get property annotations and read types from them.
        foreach ($properties as &$property) {
            // Set default accessibility on public property.
            if ($property[static::DATA_PROPERTY_VISIBILITY] == static::VISIBILITY_PUBLIC) {
                $property[static::DATA_PROPERTY_ACCESSIBILITY] = (static::ACCESSIBILITY_READ | static::ACCESSIBILITY_WRITE);
            }
            
            // Get type from @var annotation, if it exists.
            $propertyAnnotations = (array) $this->digArray($property, static::DATA_PROPERTY_ANNOTATIONS);
            foreach ($propertyAnnotations as $propertyAnnotation) {
                if ($propertyAnnotation instanceof Tag\VarTag) {
                    $property[static::DATA_PROPERTY_TYPE] = $propertyAnnotation->getType();
                }
            }
        }
        
        // Add new properties and override existing properties with info from class annotations.
        $classAnnotations = (array) $this->digArray($this->classReflectionData,
                $className, static::DATA_CLASS_ANNOTATIONS);
        foreach ($classAnnotations as $propertyTag) {
            if (!$propertyTag instanceof Tag\PropertyTag) {
                continue;
            }
            
            /* @var $propertyTag Tag\PropertyTag */
            $propertyName = trim($propertyTag->getVariableName(), '$');
            
            // Add property if it doesn't exist yet.
            if (!isset($properties[$propertyName])) {
                $properties[$propertyName] = array();
            }
            $property =& $properties[$propertyName];
            
            // Set property type.
            $property[static::DATA_PROPERTY_TYPE] = $propertyTag->getType();
            
            // Set property accessibility.
            $property[static::DATA_PROPERTY_VISIBILITY] = static::VISIBILITY_PUBLIC;
            $property[static::DATA_PROPERTY_ACCESSIBILITY] = (static::ACCESSIBILITY_READ | static::ACCESSIBILITY_WRITE);
            if ($propertyTag instanceof Tag\PropertyReadTag) {
                $property[static::DATA_PROPERTY_ACCESSIBILITY] = static::ACCESSIBILITY_READ;
            } elseif ($propertyTag instanceof Tag\PropertyWriteTag) {
                $property[static::DATA_PROPERTY_ACCESSIBILITY] = static::ACCESSIBILITY_WRITE;
            }
        }
    }
    
    /**
     * Extracts info about methods from the methods' own tags and the owning
     *  class' @method tags. Info = existence and type.
     * @internal {@see addClassTagsAsAnnotations} is supposed to have already run
     *  for the class. As is {@see addMethodTagsAsAnnotations}.
     */
    protected function extractMethodInfoFromTags($className)
    {
        // Get a pointer to the methods array.
        if (!is_array($this->digArray($this->classReflectionData, $className, static::DATA_CLASS_METHODS))) {
            $this->classReflectionData[$className][static::DATA_CLASS_METHODS] = array();
        }
        $methods =& $this->classReflectionData[$className][static::DATA_CLASS_METHODS];
        
        // Get method annotations and read types from them.
        foreach ($methods as &$method) {
            // Get type from @return annotation, if it exists.
            $methodAnnotations = (array) $this->digArray($method, static::DATA_METHOD_ANNOTATIONS);
            foreach ($methodAnnotations as $methodAnnotation) {
                if ($methodAnnotation instanceof Tag\ReturnTag) {
                    $method[static::DATA_METHOD_TYPE] = $methodAnnotation->getType();
                }
            }
        }
        
        // Add new methods and override existing methods with info from class annotations.
        $classAnnotations = (array) $this->digArray($this->classReflectionData,
                $className, static::DATA_METHOD_ANNOTATIONS);
        foreach ($classAnnotations as $methodTag) {
            if (!$methodTag instanceof Tag\MethodTag) {
                continue;
            }
            
            /* @var $methodTag Tag\MethodTag */
            $methodName = $methodTag->getMethodName();
            
            // Add method if it doesn't exist yet.
            if (!isset($methods[$methodName])) {
                $methods[$methodName] = array();
            }
            $method =& $methods[$methodName];
            
            // Set method return type.
            $method[static::DATA_METHOD_TYPE] = $methodTag->getType();
        }
    }

    protected function reflectClass($className)
    {
        parent::reflectClass($className);

        // Unfortunately, we have to create a new Reflector for the class.
        // Which has a negative effect on performance. But we cannot re-use the
        //  reflector from our parent, because it is outside our scope and
        //  Typo3\Reflection\ReflectionClass does not support getUseStatements().
        $classFinder = $this->serviceManager->get('DocBlockTags\ClassFinder');
        $reflectionParser = new StaticReflectionParser($className, $classFinder);
        
        // Add tags as annotations.
        $this->addClassTagsAsAnnotations($reflectionParser);
        $this->addPropertyTagsAsAnnotations($reflectionParser);
        $this->addMethodTagsAsAnnotations($reflectionParser);
        
        // > parse @property tags for information about properties
        $this->extractPropertyInfoFromTags($className);
        $this->extractMethodInfoFromTags($className);
    }
    
    /**
     * Digs into a nested array unto a certain value, or returns null if a key
     *  at some level does not exist.
     * @param array $array
     * @param string $keys * One or more keys
     * @return mixed
     */
    protected function digArray(array $array, $keys)
    {
        if (!is_array($keys)) {
            $keys = func_get_args();
            array_shift($keys);
        }
        foreach ($keys as $key) {
            if (!is_array($array)
                || !isset($array[$key])
            ) {
                return null;
            }
            $array = $array[$key];
        }
        
        return $array;
    }
    
    /**
     * Digs into a nested array to set a value at a certain level. Creates new
     *  empty arrays if neccessary. 
     * @param array $array
     * @param mixed $value
     * @param string $keys * One or more keys
     * @return boolean False if a level was not null and not an array.
     */
    protected function burryArray(array $array, $value, $keys)
    {
        if (!is_array($keys)) {
            $keys = func_get_args();
            array_shift($keys);
        }
        
        $lastKey = array_pop($keys);
        
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                if (!is_array($array[$key])) {
                    return false;
                }
            } else {
                $array[$key] = array();
            }
            $array = $array[$key];
        }
        
        $array[$lastKey] = $value;
    }

    public function getPropertyType($className, $propertyName)
    {
        return $this->digArray($this->classReflectionData,
                $className,
                static::DATA_CLASS_PROPERTIES,
                $propertyName,
                static::DATA_PROPERTY_TYPE);
    }
    
    protected function getPropertyVisibility($className, $propertyName)
    {
        return $this->digArray($this->classReflectionData,
                $className,
                static::DATA_CLASS_PROPERTIES,
                $propertyName,
                static::DATA_PROPERTY_VISIBILITY);
    }
    
    protected function getPropertyAccessibility($className, $propertyName)
    {
        return $this->digArray($this->classReflectionData,
                $className,
                static::DATA_CLASS_PROPERTIES,
                $propertyName,
                static::DATA_PROPERTY_ACCESSIBILITY);
    }

    public function isPropertyPublic($className, $propertyName)
    {
        if (!$this->initialized) {
            $this->initialize();
        }
        if ($className[0] === '\\') {
            $className = substr($className, 1);
        }
        $this->loadOrReflectClassIfNecessary($className);
        $visibility = $this->getPropertyVisibility($className, $propertyName);
        return $visibility === static::VISIBILITY_PUBLIC;
    }

    public function isPropertyStatic($className, $propertyName)
    {
        if (!$this->initialized) {
            $this->initialize();
        }
        if ($className[0] === '\\') {
            $className = substr($className, 1);
        }
        $this->loadOrReflectClassIfNecessary($className);
        $static = $this->digArray($this->classReflectionData,
                $className,
                static::DATA_CLASS_PROPERTIES,
                $propertyName,
                static::DATA_PROPERTY_STATIC);
        return (boolean) $static;
    }

    public function isPropertyReadable($className, $propertyName)
    {
        if (!$this->initialized) {
            $this->initialize();
        }
        if ($className[0] === '\\') {
            $className = substr($className, 1);
        }
        $this->loadOrReflectClassIfNecessary($className);
        if ($this->getPropertyVisibility($className, $propertyName) !== static::VISIBILITY_PUBLIC) {
            return false;
        }
        $accessibility = $this->getPropertyAccessibility($className, $propertyName);
        return (boolean) ($accessibility & static::ACCESSIBILITY_READ);
    }

    public function isPropertyWritable($className, $propertyName)
    {
        if (!$this->initialized) {
            $this->initialize();
        }
        if ($className[0] === '\\') {
            $className = substr($className, 1);
        }
        $this->loadOrReflectClassIfNecessary($className);
        if ($this->getPropertyVisibility($className, $propertyName) !== static::VISIBILITY_PUBLIC) {
            return false;
        }
        $accessibility = $this->getPropertyAccessibility($className, $propertyName);
        return (boolean) ($accessibility & static::ACCESSIBILITY_WRITE);
    }
    
    /**
     * Tries to find out if the class is a proxy and modify the classname to be the origin.
     * @param string $className Classname which will be changed into the origin.
     * @return boolean True if the class was a proxy.
     */
    public function eliminateProxy(&$className)
    {
        // Not use instanceof, since Doctrine\ORM\Proxy\Proxy might not be known at all.
        if (!array_search('Doctrine\ORM\Proxy\Proxy', class_implements($className))) {
            return ;
        }
        
        $className = get_parent_class($className);
    }
    
    /**
     * Checks if $haystack string ends with $needle string.
     * @param string $haystack
     * @param string $needle
     * @param boolean $case_insensitivity Be insensitive to case?
     * @return boolean TRUE if $haystack ends with $needle
     * @todo Move this function to some stdlib.
     */
    protected static function stringEndsWith($haystack, $needle, $case_insensitivity = false)
    {
        if ($needle === '') return true;
        $strlen = strlen($haystack);
        $testlen = strlen($needle);
        if ($testlen > $strlen) return false;
        return substr_compare($haystack, $needle, $strlen - $testlen, $testlen, $case_insensitivity) === 0;
    }

}
