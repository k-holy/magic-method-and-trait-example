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
use Acme\ImmutableTrait;

/**
 * TestData for BaseTrait with ImmutableTrait
 *
 * @author k.holy74@gmail.com
 */
final class BaseTraitWithImmutableTraitTestData implements BaseInterface
{
	use BaseTrait, ImmutableTrait {
		ImmutableTrait::initialize insteadof BaseTrait;
		ImmutableTrait::__set insteadof BaseTrait;
		ImmutableTrait::__unset insteadof BaseTrait;
	}

	private $string;
	private $null;
	private $boolean;
	private $datetime;

	final public function __construct(array $properties = array())
	{
		$this->initialize($properties);
	}

}
