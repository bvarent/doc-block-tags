<?php

namespace DocBlockTags\ServiceManager;

use DocBlockTags\TagReader;

/**
 * Shows dependence on a DocBlockTags\TagReader and provides setter injection.
 * 
 * @author Roel Arents <r.arents@bva-auctions.com>
 */
interface TagReaderAwareInterface
{
    /**
     * Satisfies the dependency on a Reflection Service.
     * @param TagReader $tagReader
     */
    public function setTagReader(TagReader $tagReader);
}
