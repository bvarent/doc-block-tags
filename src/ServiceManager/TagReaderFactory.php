<?php

namespace DocBlockTags\ServiceManager;

use DocBlockTags\TagReader;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Creates a \Doctrine\Common\Annotations\Reader which yields
 * \phpDocumentor\Reflection\DocBlock\Tags.
 * Uses the module config to determine
 *
 * @author bvarent <r.arents@bva-auctions.com>
 */
class TagReaderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        /* @var $config \Zend\ServiceManager\Config */
        $tagReader = new TagReader();
        foreach ($config['docblocktags']['tag_class_map'] as $tagName => $tagClassName) {
            $tagReader->registerTagClassName($tagName, $tagClassName);
        }
        
        return $tagReader;
    }

}
