<?php

namespace DocBlockTags\ServiceManager;

use DocBlockTags\ClassFinder\AggregateClassFinder;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Creates an AggregateClassFinder conforming to the module configuration.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class ClassFinderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $aggregateClassFinder = new AggregateClassFinder();
        $config = $serviceLocator->get('Config');
        /* @var $config Config */
        foreach ($config['docblocktags']['class_finders'] as $classFinderServiceName) {
            $finder = $serviceLocator->get($classFinderServiceName);
            $aggregateClassFinder->registerClassFinder($finder);
        }
        
        return $aggregateClassFinder;
    }

}
