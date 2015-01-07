<?php

namespace DocBlockTags;

return array(
    'invokables' => array(
        __NAMESPACE__ . '\ClassFinder\ComposerClassFinder' => __NAMESPACE__ . '\ClassFinder\ComposerClassFinder'
    ),
    'factories' => array(
        __NAMESPACE__ . '\ClassFinder' => __NAMESPACE__ . '\ServiceManager\ClassFinderFactory',
        __NAMESPACE__ . '\TagReader' => __NAMESPACE__ . '\ServiceManager\TagReaderFactory',
    )
);