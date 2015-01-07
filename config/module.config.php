<?php

namespace DocBlockTags;

return array(
    'docblocktags' => array(
        
        // Map of registered custom Tag classes.
        // tag name => tag class name (extending 
        'tag_class_map' => array(),
        
        // List of class finders to be used for Doctrine's Reflection Parser.
        // (The names by which the service manager knows them.)
        'class_finders' => array(
            __NAMESPACE__ . '\ClassFinder\ComposerClassFinder'
        )
    )
);