<?php

namespace DocBlockTags;

use DocBlockTags\TagAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\Reader;
use DocBlockTags\Exception;
use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Tag;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

/**
 * Machine to read Tags from DocBlocks, as if they were Annotations.
 * 
 * We distinguish these three concepts:
 * - Tag: A PhpDoc tag as defined by PhpDocumentor.
 * - Annotation: An annotation as defined by Doctrine.
 * - Tag Annotation: Technically a Tag, but treated as an Annotation.
 * 
 * So this implementation of {@see Doctrine\Common\Annotations\Reader} reads Tags,
 *  but serves them as if they were Annotations. Those tags would normally be
 *  ignored by Doctrine's AnnotationReader.
 * 
 * Custom Tag classes can be registered, to complement and/or override
 *  PhpDocumentor's built-in classes.
 * 
 * Make sure to use \Doctrine\Common\Reflection\StaticReflectionClass, since it
 *  also parses use statements. That way the types in e.g. @param tags will be
 *  correctly namespaced.
 */
class TagReader implements Reader
{
    /**
     * Map of tag names to the class names which represent them.
     * @var Map<string, string>
     */
    protected $tagClassNameMap = array();
    
    /**
     * Registers a class to be instantiated when a Tag with some name is found.
     * This is on top of the registered classes in phpDocumentor\Reflection\DocBlock\Tag.
     * @param string $tagName
     * @param string $tagClassName
     */
    public function registerTagClassName($tagName, $tagClassName)
    {
        if (!class_exists($tagClassName)) {
            throw new InvalidArgumentException('The tag class' . $tagClassName
                    . ' does not exist.');
        }
        
        if (!is_a($tagClassName, 'phpDocumentor\Reflection\DocBlock\Tag', true)) {
            throw new InvalidArgumentException('The tag class' . $tagClassName
                    . ' should extend phpDocumentor\Reflection\DocBlock\Tag.');
        }
        
        $this->tagClassNameMap[$tagName] = $tagClassName;
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

    /**
     * Try to restore the case in Use Statements aliases. Since Doctrine's
     *  StaticReflectionParser serves lower case, but phpDocumentor is case
     *  sensitive.
     * @todo Think of something better. This does not cover 'use Space\Class as Alias'.
     */
    protected function caseUseStatements(array & $useStatements)
    {
        $casedStatements = array();
        foreach ($useStatements as $alias => $full)
        {
            if (static::stringEndsWith($full, $alias, true)) {
                $casedAlias = substr($full, -strlen($alias));
                if ($casedAlias !== $alias) {
                    $casedStatements[$casedAlias] = $full;
                }
            }
        }
        
        $useStatements = array_merge($casedStatements, $useStatements);
    }
    
    /**
     * Tries to get as much info about the Context from a Reflector.
     * @param Reflector $reflector
     * @return Context
     */
    public function getContextFromReflector(Reflector $reflector)
    {
        if ($reflector instanceof \ReflectionClass) {
            $classReflector = $reflector;
            $className = $classReflector->getName();
            $this->classReflectors[$className] = $classReflector;
        } elseif ($reflector instanceof \ReflectionProperty
                || $reflector instanceof \ReflectionMethod
        ) {
            $classReflector = $reflector->getDeclaringClass();
        }
        
        $namespace = null;
        if (method_exists($classReflector, 'getNamespaceName')) {
            $namespace = $classReflector->getNamespaceName();
        }
        $useStatements = array();
        if (method_exists($classReflector, 'getUseStatements')) {
            $useStatements = $classReflector->getUseStatements();
            $this->caseUseStatements($useStatements);
        }
        else {
            trigger_error('Could not reliably determine the context of the tag. '
                    . 'Make sure to use a ReflectionClass which supports getUseStatements.',
                    E_USER_WARNING);
        }
        
        $context = new Context($namespace, $useStatements);
        
        return $context;
    }
    
    /**
     * Creates (or keeps) a Tag from a phpDocumentor Tag.
     *  If a custom Tag class was registered, it has priority.
     * @param Tag $tag
     * @return Tag
     */
    protected function createTagAnnotation(Tag $tag)
    {
        $name = $tag->getName();
        
        // If our own map of tags doesn't have a registered class, keep the original tag.
        if (isset($this->tagClassNameMap[$name])) {
            $tagClassName = $this->tagClassNameMap[$name];
            $annotationTag = new $tagClassName(
                    $tag->getName(), 
                    $tag->getContent(), 
                    $tag->getDocBlock(), 
                    $tag->getLocation() );
        }
        else {
            $annotationTag = $tag;
        }
        
        return $annotationTag;
    }
    
    /**
     * Figures out a Tag's target from its reflector which provided the DocBlock.
     * @param Reflector $reflector E.g. a ReflectionClass or ReflectionMethod
     * @return int A constant from Target specifying the target of the tag.
     */
    public function getTarget(Reflector $reflector)
    {
        switch (true) {
            case ($reflector instanceof \ReflectionClass):
                return Target::TARGET_CLASS;
            case ($reflector instanceof \ReflectionMethod):
                return Target::TARGET_METHOD;
            case ($reflector instanceof \ReflectionProperty):
                return Target::TARGET_PROPERTY;
            default:
                return null;
        }
    }
    
    /**
     * Gets all Tag Annotations in a DocBlock.
     * @param Reflector $reflector Supporting the getDocComment method.
     * @return Tag[]
     * @todo Perhaps try to recognize a Tag as an Annotation and ignore it?
     */
    protected function getTagAnnotations(Reflector $reflector)
    {
        $context = $this->getContextFromReflector($reflector);
        $docBlock = new DocBlock($reflector, $context);
        $tags = $docBlock->getTags();
        
        $tagAnnotations = array();
        foreach ($tags as $tag) {
            /* @var $tag Tag */
            $tagAnnotation = $this->createTagAnnotation($tag);
            if ($tagAnnotation) {
                $tagAnnotations[] = $tagAnnotation;
            }
        }
        
        return $tagAnnotations;
    }
    
    /**
     * Gets the first Tag Annotation matching the requested name.
     * @param Reflector $reflector
     * @param string $annotationName
     * @return Tag
     */
    protected function getTagAnnotation(Reflector $reflector, $annotationName)
    {
        $allTagAnnotations = $this->getClassAnnotations($reflector);
        foreach ($allTagAnnotations as $tagAnnotation) {
            /* @var $tagAnnotation Tag */
            if ($annotationName == $tagAnnotation->getName()) {
                return $tagAnnotation;
            }
        }
    }
    
    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        return $this->getTagAnnotation($class, $annotationName);
    }

    public function getClassAnnotations(ReflectionClass $class)
    {
        $tagAnnotations = $this->getTagAnnotations($class);
        
        return $tagAnnotations;
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        return $this->getTagAnnotation($method, $annotationName);
    }

    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $tagAnnotations = $this->getTagAnnotations($method);
        
        return $tagAnnotations;
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        return $this->getTagAnnotation($property, $annotationName);
    }

    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        $tagAnnotations = $this->getTagAnnotations($property);
        
        return $tagAnnotations;
    }

}
