<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Domain\Data;

use Acme\BaseInterface;
use Acme\BaseTrait;
use Acme\Domain\Data\UserTrait;

/**
 * MutableUser
 *
 * @author k.holy74@gmail.com
 */
class MutableUser implements BaseInterface, \JsonSerializable
{
	use BaseTrait;
	use UserTrait;

	private $userId;
	private $userName;
	private $createdAt;
	private $timezone;
	private $dateFormat;

	public function __construct(array $properties = null, \DateTimeZone $timezone = null, $dateFormat = null)
	{
		if (isset($properties)) {
			$this->initialize($properties);
		}
		if (isset($timezone)) {
			$this->timezone = $timezone;
		}
		$this->dateFormat = $dateFormat ?: 'Y-m-d H:i:s';
	}

}
