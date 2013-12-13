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

	/**
	 * @var string
	 */
	private $userId;

	/**
	 * @var string
	 */
	private $userName;

	/**
	 * @var string タイムスタンプ値
	 */
	private $createdAt;

	/**
	 * @var \DateTimeZone 日付の出力用タイムゾーン
	 */
	private $timezone;

	/**
	 * @var string 日付の出力用書式
	 */
	private $dateFormat;

}
