<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test\Domain\Data;

use Acme\Domain\Data\DataInterface;
use Acme\Domain\Data\DataTrait;

/**
 * TestData for DataTrait
 *
 * @author k.holy74@gmail.com
 */
final class DataTraitTestData implements DataInterface, \JsonSerializable
{
	use DataTrait;

	private $string;
	private $null;
	private $boolean;
	private $datetime;
	private $datetimeImmutable;
	private $dateFormat;

	/**
	 * @param \DateTime
	 */
	private function setDateTime(\DateTime $datetime)
	{
		$this->datetime = $datetime;
	}

	/**
	 * @param \DateTimeImmutable
	 */
	private function setDateTimeImmutable(\DateTimeImmutable $datetimeImmutable)
	{
		$this->datetimeImmutable = $datetimeImmutable;
	}

	/**
	 * 日付の出力用書式をセットします。
	 *
	 * @param string
	 */
	private function setDateFormat($dateFormat)
	{
		$this->dateFormat = $dateFormat;
	}

	/**
	 * @return string
	 */
	public function getDatetimeAsString()
	{
		return (isset($this->datetime)) ? $this->datetime->format($this->dateFormat ?: 'Y-m-d H:i:s') : null;
	}

	/**
	 * @return string
	 */
	public function getDatetimeImmutableAsString()
	{
		return (isset($this->datetimeImmutable)) ? $this->datetimeImmutable->format($this->dateFormat ?: 'Y-m-d H:i:s') : null;
	}

	/**
	 * JsonSerializable::jsonSerialize
	 *
	 * @return \stdClass for json_encode()
	 */
	public function jsonSerialize()
	{
		$object = new \stdClass;
		$object->string = $this->string;
		$object->null = $this->null;
		$object->boolean = $this->boolean;
		$object->datetime = $this->getDatetimeAsString();
		$object->datetimeImmutable = $this->getDatetimeImmutableAsString();
		return $object;
	}

}
