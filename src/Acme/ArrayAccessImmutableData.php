<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

final class ArrayAccessImmutableData implements BaseInterface, \ArrayAccess
{
	use BaseTrait, ArrayAccessTrait, ImmutableTrait {
		ImmutableTrait::initialize insteadof BaseTrait;
		ImmutableTrait::__set insteadof BaseTrait;
		ImmutableTrait::__unset insteadof BaseTrait;
	}

	private $string;
	private $null;
	private $boolean;
	private $datetime;
	private $array;

	final public function __construct(array $properties = array())
	{
		$this->initialize($properties);
	}

}
