<?php

namespace DocBlockTags\ServiceManager;

use DocBlockTags\Hydrator\ReflectionServiceHydrator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Creates a ReflectionServiceHydrator and injects dependencies.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class ReflectionServiceHydratorFactory implements FactoryInterface
{
    
    public function createService(ServiceLocatorInterface $hydratorServiceLocator)
    {
        $serviceManager = $hydratorServiceLocator->getServiceLocator();
        /* @var $serviceManager ServiceManager */
        $reflServ = $serviceManager->get('DocBlockTags\Reflection\ReflectionService');
        $reflServHydrator = new ReflectionServiceHydrator();
        $reflServHydrator->setReflectionService($reflServ);
        
        return $reflServHydrator;
    }

}
