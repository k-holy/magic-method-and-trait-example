<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

use Acme\JsonSerializer;

/**
 * JsonSerializableTrait
 *
 * @author k.holy74@gmail.com
 */
trait JsonSerializableTrait
{

	/**
	 * JsonSerializable::jsonSerialize
	 *
	 * @return object
	 */
	public function jsonSerialize()
	{
		$object = new \stdClass;
		$jsonSerializer = new JsonSerializer();
		foreach (get_object_vars($this) as $name => $val) {
			$object->{$name} = $jsonSerializer($val);
		}
		return $object;
	}

}
