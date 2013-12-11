<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

/**
 * Test for ImmutableTrait
 *
 * @author k.holy74@gmail.com
 */
class ImmutableTraitTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructorDefensiveCopy()
	{
		$now = new \DateTime();
		$test = new ImmutableTraitTestData([
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
		$test = new ImmutableTraitTestData([
			'undefined_property' => 'Foo',
		]);
	}

	public function testIsset()
	{
		$test = new ImmutableTraitTestData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertTrue(isset($test->string));
		$this->assertFalse(isset($test->null));
		$this->assertFalse(isset($test->undefined_property));
	}

	public function testGet()
	{
		$test = new ImmutableTraitTestData([
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
		$test = new ImmutableTraitTestData();
		$test->undefined_property;
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetRaiseLogicException()
	{
		$test = new ImmutableTraitTestData([
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
		$test = new ImmutableTraitTestData();
		$test->undefined_property = 'Foo';
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testUnsetRaiseLogicException()
	{
		$test = new ImmutableTraitTestData([
			'string' => 'Foo',
		]);
		unset($test->string);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testUnsetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new ImmutableTraitTestData();
		unset($test->undefined_property);
	}

}
