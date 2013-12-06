<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

class TraversableData implements BaseInterface, \IteratorAggregate
{
	use BaseTrait;
	use TraversableDataTrait;

	private $string;
	private $null;
	private $boolean;
	private $datetime;

	public function __construct(array $properties = array())
	{
		$this->initialize($properties);
	}

}
