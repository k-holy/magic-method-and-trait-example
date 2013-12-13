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

/**
 * TestData for BaseTrait
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitTestData implements BaseInterface
{
    use BaseTrait;

    private $string;
    private $null;
    private $boolean;
    private $datetime;

    public function __construct(array $properties = array())
    {
        $this->initialize($properties);
    }

}
