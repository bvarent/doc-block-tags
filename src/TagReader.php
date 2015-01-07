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
 * Reads tags from docblocks. Returns them as instantions of TagAnnotation.
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
     * Registers a class to be instantiated when a tag with some name is found.
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
     * Tries to get as much info about the Context from a Reflector.
     * @param Reflector $reflector
     * @return Context
     */
    public function getContextFromReflector(Reflector $reflector)
    {
        $class = $reflector;
        if (method_exists($reflector, 'getDeclaringClass')) {
            $class = $reflector->getDeclaringClass();
        }
        $namespace = null;
        if (method_exists($reflector, 'getNamespaceName')) {
            $namespace = $reflector->getNamespaceName();
        }
        $useStatements = array();
        if (method_exists($reflector, 'getUseStatements')) {
            $useStatements = $reflector->getUseStatements();
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
     * Creates (or keeps) a tag from a phpDocumentor Tag.
     * @param Tag $tag
     * @return Tag
     * @throws Exception
     */
    protected function createAnnotationTag(Tag $tag)
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
     * Figures out a tag's target from its reflector which provided the DocBlock.
     * @param Reflector $reflector E.g. a ReflectionClass or ReflectionMethod
     * @return int A constant from Target specifying the target of the tag.
     */
    protected function getTarget(Reflector $reflector)
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
     * Gets all Tag Annotations in a docblock
     * @param Reflector $reflector Supporting the getDocComment method.
     * @return TagAnnotation[]
     */
    protected function getTagAnnotations(Reflector $reflector)
    {
        $context = $this->getContextFromReflector($reflector);
        $docBlock = new DocBlock($reflector, $context);
        $tags = $docBlock->getTags();
        
        $tagAnnotations = array();
        foreach ($tags as $tag) {
            /* @var $tag Tag */
            $tagAnnotation = $this->createAnnotationTag($tag);
            if ($tagAnnotation) {
                $tagAnnotations[] = $tagAnnotation;
            }
        }
        
        return $tagAnnotations;
    }
    
    /**
     * Gets the first annotation matching the requested name.
     * @param Reflector $reflector
     * @param type $annotationName
     * @return type
     */
    protected function getTagAnnotation(Reflector $reflector, $annotationName)
    {
        $allTagAnnotations = $this->getClassAnnotations($reflector);
        foreach ($allTagAnnotations as $tagAnnotation) {
            /* @var $tagAnnotation TagAnnotation */
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
