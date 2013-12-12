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

}
