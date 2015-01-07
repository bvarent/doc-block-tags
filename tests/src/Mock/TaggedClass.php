<?php

namespace DocBlockTags\Tests\Mock;

use Foo\SomeClass;

/**
 * @property SomeClass $someClass A property of some class.
 */
class TaggedClass
{

    /** @var int */
    public $intProp;

    /**
     * @var bool
     */
    public $boolProp;

    /**
     * @var float
     */
    public $floatProp;

    /**
     * There might be some text here.
     * @var string or here.
     * Or here.
     */
    public $stringProp;

    /**
     * @var array
     */
    public $arrayProp;

    /**
     * @var binary
     */
    public $binaryProp;

    /**
     * @var Mock\Enum
     */
    public $enumProp;

    /**
     * The type is denoted with its full namespace,
     *  because it is not in the Entity namespace.
     * @var Mock
     */
    public $objectProp;

    /**
     * Lists can be defined by adding [] behind the type.
     * @var int[]
     */
    public $intListProp;

    /**
     * Lists can also be defined by encapsulating the type in List< >
     * @var List<bool> There might be some text here.
     */
    public $boolListProp;

    /**
     * @var float[]
     */
    public $floatListProp;

    /**
     * @var List<string>
     */
    public $stringListProp;

    /**
     * @var array[]
     */
    public $arrayListProp;

    /**
     * @var binary[]
     */
    public $binaryListProp;

    /**
     * @var array<Mock>
     */
    public $objectListProp;

}
