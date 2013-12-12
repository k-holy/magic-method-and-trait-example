<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Domain\Data;

/**
 * UserTrait
 *
 * @author k.holy74@gmail.com
 */
trait UserTrait
{

	/**
	 * __construct()
	 *
	 * @param array プロパティの配列
	 * @param \DateTimeZone タイムゾーン
	 * @param string 日付の出力書式
	 */
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

	/**
	 * getter for createdAt
	 *
	 * 値をタイムスタンプと見なし、指定されたタイムゾーンおよび書式で文字列に変換して返します。
	 *
	 * @return string
	 */
	public function getCreatedAt()
	{
		$createdAt = ($this->createdAt instanceof \DateTime)
			? $this->createdAt
			: new \DateTime(sprintf('@%d', $this->createdAt));
		if (isset($this->timezone)) {
			$createdAt->setTimezone($this->timezone);
		}
		return $createdAt->format($this->dateFormat);
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
		$object->userId = $this->userId;
		$object->userName = $this->userName;
		$object->createdAt = $this->getCreatedAt();
		return $object;
	}

}
