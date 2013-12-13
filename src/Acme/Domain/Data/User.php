<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Domain\Data;

use Acme\Domain\Data\DataInterface;
use Acme\Domain\Data\DataTrait;

/**
 * User
 *
 * @author k.holy74@gmail.com
 */
class User implements DataInterface, \IteratorAggregate, \JsonSerializable
{

	use DataTrait;

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
		return (isset($this->timezone)) 
			? $this->createdAt->setTimezone($this->timezone)
			: $this->createdAt;
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
