<?php

namespace DocBlockTags\Hydrator;

use DocBlockTags\Reflection\ReflectionServiceInterface;
use DocBlockTags\ServiceManager\ReflectionServiceAwareInterface;
use Zend\Stdlib\Hydrator\AbstractHydrator;

/**
 * This ZF2 Hydrator uses a Reflection Service to retrieve information about
 *  readable and writable properties of objects.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class ReflectionServiceHydrator extends AbstractHydrator implements ReflectionServiceAwareInterface
{
    /**
     * @var ReflectionServiceInterface
     */
    protected $reflectionService;

    public function setReflectionService(ReflectionServiceInterface $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }
    
    public function extract($object)
    {
        // Find out all properties using the Reflection Service.
        $extracted = array();
        $reflService = $this->reflectionService;
        $className = get_class($object);
        $reflService->eliminateProxy($className);
        $propertyNames = $reflService->getClassPropertyNames($className);
        
        // Extract all readable properties, respecting filters and strategies.
        foreach ($propertyNames as $propertyName) {
            if ($reflService->isPropertyReadable($className, $propertyName)) {
                $propertyExtractName = $this->extractName($propertyName, $object);
                if (!$this->filterComposite->filter($propertyExtractName)) {
                    continue;
                }
                $value = $object->{$propertyExtractName};
                $extracted[$propertyExtractName] = $this->extractValue($propertyExtractName, $value, $object);
            }
        }

        return $extracted;
    }

    public function hydrate(array $input, $hydratee)
    {
        // Find out all properties using the Reflection Service.
        $reflService = $this->reflectionService;
        $className = get_class($hydratee);
        $reflService->eliminateProxy($className);
        
        // Hydrate all writable properties, respecting filters and strategies.
        foreach ($input as $inputKey => $inputValue) {
            $propertyHydrateName = $this->hydrateName($inputKey, $input);
            if ($reflService->isPropertyWritable($className, $propertyHydrateName)) {
                $hydratee->$propertyHydrateName = $this->hydrateValue($propertyHydrateName, $inputValue, $input);
            }
        }
        
        return $hydratee;
    }

}
