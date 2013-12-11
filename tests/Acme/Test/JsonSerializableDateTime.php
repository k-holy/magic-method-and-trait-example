<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

/**
 * JsonSerializableDateTime
 *
 * @author k.holy74@gmail.com
 */
class JsonSerializableDateTime extends \DateTime implements \JsonSerializable
{

	private $format;

	public function setFormat($format)
	{
		$this->format = $format;
	}

	public function jsonSerialize()
	{
		return $this->format($this->format ?: \DateTime::RFC3339);
	}
}
