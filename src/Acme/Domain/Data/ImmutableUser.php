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
use Acme\ImmutableTrait;
use Acme\Domain\Data\UserTrait;

/**
 * ImmutableUser
 *
 * @author k.holy74@gmail.com
 */
class ImmutableUser implements BaseInterface, \JsonSerializable
{
	use BaseTrait, ImmutableTrait, UserTrait {
		ImmutableTrait::initialize insteadof BaseTrait;
		ImmutableTrait::__set insteadof BaseTrait;
		ImmutableTrait::__unset insteadof BaseTrait;
	}

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
