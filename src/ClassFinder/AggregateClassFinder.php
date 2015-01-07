<?php

namespace DocBlockTags\ClassFinder;

use Doctrine\Common\Reflection\ClassFinderInterface;

/**
 * Combines multiple class finders and tries each to find a class' source file.
 *
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
class AggregateClassFinder implements ClassFinderInterface
{

    /**
     * List of registered class finders instances.
     * @var ClassFinderInterface[]
     */
    protected $finders;

    /**
     * Registers a class finder to use.
     * @param ClassFinderInterFace $finder
     */
    public function registerClassFinder(ClassFinderInterFace $finder)
    {
        if (!is_a($finder, get_called_class(), false)) {
            $this->finders[] = $finder;
        }
    }

    public function findFile($class)
    {
        foreach ($this->finders as $finder) {
            /* @var $finder ClassFinderInterface */
            if ($found = $finder->findFile($class)) {
                return $found;
            }
        }
    }

}
