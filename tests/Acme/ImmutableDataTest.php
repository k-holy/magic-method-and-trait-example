<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Tests;

use Acme\ImmutableData;

/**
 * Test for ImmutableData
 *
 * @author k.holy74@gmail.com
 */
class ImmutableDataTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructorDefensiveCopy()
	{
		$now = new \DateTime();
		$test = new ImmutableData([
			'datetime' => $now,
		]);
		$this->assertEquals($now, $test->datetime);
		$this->assertNotSame($now, $test->datetime);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructorRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new ImmutableData([
			'undefined_property' => 'Foo',
		]);
	}

	public function testIsset()
	{
		$test = new ImmutableData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertTrue(isset($test->string));
		$this->assertFalse(isset($test->null));
		$this->assertFalse(isset($test->undefined_property));
	}

	public function testGet()
	{
		$test = new ImmutableData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertEquals('Foo', $test->string);
		$this->assertNull($test->null);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new ImmutableData();
		$test->undefined_property;
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetRaiseLogicException()
	{
		$test = new ImmutableData([
			'string'  => 'Foo',
			'boolean' => true,
		]);
		$test->string = 'Bar';
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new ImmutableData();
		$test->undefined_property = 'Foo';
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testUnsetRaiseLogicException()
	{
		$test = new ImmutableData([
			'string' => 'Foo',
		]);
		unset($test->string);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testUnsetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new ImmutableData();
		unset($test->undefined_property);
	}

}
