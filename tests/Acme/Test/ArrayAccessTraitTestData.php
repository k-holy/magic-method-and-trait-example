<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\ArrayAccessTrait;

/**
 * TestData for ArrayAccessTrait
 *
 * @author k.holy74@gmail.com
 */
class ArrayAccessTraitTestData implements \ArrayAccess
{
    use ArrayAccessTrait;

    private $string;
    private $null;
    private $boolean;
    private $datetime;

}
