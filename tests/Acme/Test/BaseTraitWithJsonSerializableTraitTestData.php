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
use Acme\JsonSerializableTrait;

/**
 * TestData for BaseTrait with JsonSerializableTrait
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitWithJsonSerializableTraitTestData implements BaseInterface, \JsonSerializable
{
	use BaseTrait;
	use JsonSerializableTrait;

	private $string;
	private $null;
	private $boolean;
	private $datetime;
	private $array;
	private $object;
	private $iterator;
	private $serializable;
	private $any;

	public function __construct(array $properties = array())
	{
		$this->initialize($properties);
	}

}
