<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Tests;

use Acme\FormatData;

/**
 * Test for FormatData
 *
 * @author k.holy74@gmail.com
 */
class FormatDataTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructor()
	{
		$now = new \DateTime();
		$test = new FormatData([
			'savedDate' => $now,
		]);
		$this->assertEquals($now, $test->savedDate);
		$this->assertSame($now, $test->savedDate);
	}

	public function testSetSavedDate()
	{
		$now = new \DateTime();
		$test = new FormatData([
			'savedDate' => $now,
		]);
		$this->assertSame($now, $test->savedDate);
		$test->savedDate = new \DateTime();
		$this->assertNotSame($now, $test->savedDate);
	}

	public function testSetSavedDateByTimestamp()
	{
		$now = new \DateTime();
		$test = new FormatData([
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
		$test = new FormatData([
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
		$test = new FormatData([
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
		$test = new FormatData([
			'savedDate' => new \stdClass(),
		]);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetSavedDateRaiseInvalidArgumentExceptionInvalidType()
	{
		$test = new FormatData([
			'savedDate' => true,
		]);
	}

	public function testGetSavedDateAsString()
	{
		$now = new \DateTime();
		$test = new FormatData([
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
		$test = new FormatData([
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

}
