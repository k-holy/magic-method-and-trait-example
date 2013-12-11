<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme;

use Acme\BaseInterface;
use Acme\BaseTrait;

/**
 * User
 *
 * @author k.holy74@gmail.com
 */
class User implements BaseInterface, \JsonSerializable
{
	use BaseTrait;

	private $user_id;
	private $user_name;
	private $created_at;
	private $dateFormat;

	public function __construct(array $properties = null, $dateFormat = null)
	{
		if (isset($properties)) {
			$this->initialize($properties);
		}
		$this->dateFormat = $dateFormat ?: 'Y-m-d H:i:s';
	}

	/**
	 * JsonSerializable::jsonSerialize
	 *
	 * @return object
	 * @throws \LogicException
	 */
	public function jsonSerialize()
	{
		$object = new \stdClass;
		$object->user_id = $this->user_id;
		$object->user_name = $this->user_name;
		$created_at = new \DateTime($this->created_at);
		$object->created_at = $created_at->format($this->dateFormat);
		return $object;
	}

}
