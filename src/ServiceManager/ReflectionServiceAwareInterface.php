<?php

namespace DocBlockTags\ServiceManager;

use DocBlockTags\Reflection\ReflectionServiceInterface;

/**
 * Shows dependence on a ReflectionService and provides setter injection.
 * 
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
interface ReflectionServiceAwareInterface
{
    /**
     * Satisfies the dependency on a Reflection Service.
     * @param ReflectionServiceInterface $reflectionService
     */
    public function setReflectionService(ReflectionServiceInterface $reflectionService);
}
