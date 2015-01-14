<?php

namespace DocBlockTags;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

    public function getAutoloaderConfig()
    {
        // Composer takes care of the autoloading.
        return array();
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return include __DIR__ . '/../config/service.config.php';
    }
    
    public function getHydratorConfig()
    {
        return array(
            'aliases' => array(
                'reflectionservice' => __NAMESPACE__ . '\Hydrator\ReflectionServiceHydrator',
            ),
            'factories' => array(
                __NAMESPACE__ . '\Hydrator\ReflectionServiceHydrator' => __NAMESPACE__ . '\ServiceManager\ReflectionServiceHydratorFactory',
            ),
        );
    }

}
