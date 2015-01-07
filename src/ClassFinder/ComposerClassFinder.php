<?php

namespace DocBlockTags\ClassFinder;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Reflection\ClassFinderInterface;
use DocBlockTags\Exception\Exception;

/**
 * Finds a class's source filename using Composer's autoloader.
 */
class ComposerClassFinder implements ClassFinderInterface
{

    /**
     * The Composer autoloader.
     * @var ClassLoader
     */
    protected $loader;
    
    /**
     * Gets Composer's class loader.
     * @return ClassLoader
     */
    public function getLoader()
    {
        if (!isset($this->loader)) {
            $composerDir = $this->getComposerDir();
            $vendorDir = $composerDir . DIRECTORY_SEPARATOR . 'vendor';
            $loaderFile = $vendorDir . DIRECTORY_SEPARATOR . 'autoload.php';
            $this->loader = include $loaderFile;
        }
        
        return $this->loader;
    }
    
    /**
     * Finds the nearest ancestor directory containing the composer.json config.
     * @return string
     */
    public function getComposerDir()
    {
        $dir = __DIR__;
        do {
            $composerConfigFilePath = $dir . DIRECTORY_SEPARATOR . 'composer.json';
            if (is_file($composerConfigFilePath)) {
                return $dir;
            }
        } while ($dir = dirname($dir));
        
        throw new Exception('Composer root directory was not found.');
    }

    public function findFile($class)
    {
        $loader = $this->getLoader();
        
        return $loader->findFile($class);
    }

}
