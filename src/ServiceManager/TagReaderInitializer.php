<?php

namespace DocBlockTags\ServiceManager;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Provides a TagReaderAware class with a TagReader service.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class TagReaderInitializer implements InitializerInterface
{

    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof TagReaderAwareInterface) {
            $tagReader = $serviceLocator->get('DocBlockTags\TagReader');
            $instance->setTagReader($tagReader);
        }
    }

}
