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
final class DataTraitTestData implements DataInterface, \IteratorAggregate, \JsonSerializable
{
	use DataTrait;

	private $string;
	private $null;
	private $boolean;
	private $datetime;
	private $dateFormat;

	/**
	 * @param \DateTime
	 */
	private function setDateTime(\DateTime $datetime)
	{
		$this->datetime = $datetime;
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
		return $object;
	}

}
