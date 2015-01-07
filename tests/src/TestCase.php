<?php

namespace DocBlockTags\Tests;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Base testcase 
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Gets the root directory containing the tests.
     * @return string
     */
    public static function getTestDir()
    {
       return realpath(dirname(__DIR__)); 
    }
    
    /**
     * @return ServiceLocatorInterface
     */
    public static function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

}
