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

/**
 * User
 *
 * @author k.holy74@gmail.com
 */
class User implements BaseInterface, \JsonSerializable
{
	use BaseTrait, ImmutableTrait {
		ImmutableTrait::initialize insteadof BaseTrait;
		ImmutableTrait::__set insteadof BaseTrait;
		ImmutableTrait::__unset insteadof BaseTrait;
	}

	/**
	 * @var int
	 */
	private $userId;

	/**
	 * @var string
	 */
	private $userName;

	/**
	 * @var \DateTimeImmutable
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

	/**
	 * __construct()
	 *
	 * @param array プロパティの配列
	 */
	public function __construct(array $properties = null)
	{
		if (isset($properties)) {
			$this->initialize($properties);
		}
	}

	/**
	 * createdAtの値をセットします。
	 *
	 * @param \DateTimeImmutable
	 */
	private function setCreatedAt(\DateTimeImmutable $createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * 日付の出力用タイムゾーンをセットします。
	 *
	 * @param \DateTimeZone
	 */
	private function setTimezone(\DateTimeZone $timezone)
	{
		$this->timezone = $timezone;
	}

	/**
	 * 日付の出力用書式をセットします。
	 *
	 * @param \DateTimeZone
	 */
	private function setDateFormat($dateFormat)
	{
		$this->dateFormat = $dateFormat ?: 'Y-m-d H:i:s';
	}

	/**
	 * createdAtの値に出力用のTimezoneをセットして返します。
	 *
	 * @return \DateTimeImmutable
	 */
	public function getCreatedAt()
	{
		if (isset($this->timezone)) {
			return $this->createdAt->setTimezone($this->timezone);
		}
		return $this->createdAt;
	}

	/**
	 * createdAtの値を出力用の書式で文字列に変換して返します。
	 *
	 * @return string
	 */
	public function getCreatedAtAsString()
	{
		return $this->getCreatedAt()->format($this->dateFormat);
	}

	/**
	 * JsonSerializable::jsonSerialize
	 *
	 * @return \stdClass for json_encode()
	 */
	public function jsonSerialize()
	{
		$object = new \stdClass;
		$object->userId = $this->userId;
		$object->userName = $this->userName;
		$object->createdAt = $this->getCreatedAtAsString();
		return $object;
	}

}
