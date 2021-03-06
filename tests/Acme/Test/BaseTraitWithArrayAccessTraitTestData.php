<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

use Acme\BaseInterface;
use Acme\BaseTrait;
use Acme\ArrayAccessTrait;

/**
 * TestData for BaseTrait with ArrayAccessTrait
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitWithArrayAccessTraitTestData implements BaseInterface, \ArrayAccess
{
    use BaseTrait;
    use ArrayAccessTrait;

    private $string;
    private $null;
    private $boolean;
    private $datetime;

    public function __construct(array $properties = array())
    {
        $this->initialize($properties);
    }

}
