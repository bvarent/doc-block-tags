<?php

namespace DocBlockTags\Tests\Mock;

use Foo\SomeClass;

/**
 * 
 * @property SomeClass $someProp A property typed Foo\SomeClass with multi-line de-
 *  scription.
 * @property-write SomeClass $someWriteOnlyProp A write-only property typed Foo\SomeClass.
 * @property-read SomeClass $someReadOnlyProp A readonly property typed Foo\SomeClass.
 */
class TaggedClass
{

    /** @var int */
    public $intProp;

    /**
     * @var bool
     */
    private $boolProp;

    /**
     * @var float
     */
    protected $floatProp;

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
