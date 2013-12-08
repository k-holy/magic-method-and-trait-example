<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Tests;

use Acme\FormatImmutableData;

/**
 * Test for FormatImmutableData
 *
 * @author k.holy74@gmail.com
 */
class FormatImmutableDataTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructorDefensiveCopy()
	{
		$now = new \DateTime();
		$test = new FormatImmutableData([
			'savedDate' => $now,
		]);
		$this->assertEquals($now, $test->savedDate);
		$this->assertNotSame($now, $test->savedDate);
	}

	public function testSetSavedDateByTimestamp()
	{
		$now = new \DateTime();
		$test = new FormatImmutableData([
			'savedDate' => $now->getTimestamp(),
		]);
		$this->assertInstanceOf('\DateTime', $test->savedDate);
		$this->assertEquals(
			$now->getTimestamp(),
			$test->savedDate->getTimestamp()
		);
	}

	public function testSetSavedDateByString()
	{
		$now = new \DateTime();
		$test = new FormatImmutableData([
			'savedDate' => $now->format('Y-m-d H:i:s'),
		]);
		$this->assertInstanceOf('\DateTime', $test->savedDate);
		$this->assertEquals(
			$now->format('Y-m-d H:i:s'),
			$test->savedDate->format('Y-m-d H:i:s')
		);
	}

	public function testSetSavedDateByStringWithTimezone()
	{
		$now = new \DateTime();
		$test = new FormatImmutableData([
			'savedDate' => $now->format('Y-m-d H:i:s'),
			'options' => [
				'timezone' => new \DateTimeZone('Asia/Tokyo'),
			],
		]);
		$this->assertInstanceOf('\DateTime', $test->savedDate);
		$this->assertEquals(
			$now->format('Y-m-d H:i:s'),
			$test->savedDate->format('Y-m-d H:i:s')
		);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSavedDateRaiseInvalidArgumentExceptionInvalidObject()
	{
		$test = new FormatImmutableData([
			'savedDate' => new \stdClass(),
		]);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSavedDateRaiseInvalidArgumentExceptionInvalidType()
	{
		$test = new FormatImmutableData([
			'savedDate' => true,
		]);
	}

	public function testGetSavedDateAsString()
	{
		$now = new \DateTime();
		$test = new FormatImmutableData([
			'savedDate' => $now,
		]);
		$this->assertEquals(
			$now->format('Y-m-d H:i:s'),
			$test->savedDateAsString
		);
	}

	public function testGetSavedDateAsStringWithDateTimeFormat()
	{
		$now = new \DateTime();
		$test = new FormatImmutableData([
			'savedDate' => $now,
			'options' => [
				'dateTimeFormat' => 'Y/n/j H:i:s',
			],
		]);
		$this->assertEquals(
			$now->format('Y/n/j H:i:s'),
			$test->savedDateAsString
		);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new FormatImmutableData();
		$test->undefined_property;
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetRaiseLogicException()
	{
		$test = new FormatImmutableData([
			'savedDate' => new \DateTime(),
		]);
		$test->savedDate = new \DateTime();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new FormatImmutableData();
		$test->undefined_property = 'Foo';
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testUnsetRaiseLogicException()
	{
		$test = new FormatImmutableData([
			'savedDate' => new \DateTime(),
		]);
		unset($test->savedData);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testUnsetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new FormatImmutableData();
		unset($test->undefined_property);
	}

}
