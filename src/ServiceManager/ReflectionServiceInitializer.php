<?php

namespace DocBlockTags\ServiceManager;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Provides a ReflectionServiceAware class with a ReflectionService instance.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class ReflectionServiceInitializer implements InitializerInterface
{

    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof ReflectionServiceAwareInterface) {
            $reflectionService = $serviceLocator->get('DocBlockTags\ReflectionService');
            $instance->setReflectionService($reflectionService);
        }
    }

}
