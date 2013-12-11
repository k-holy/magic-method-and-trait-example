<?php
/**
 * magic-method-and-trait-example
 *
 * @copyright 2013 k-holy <k.holy74@gmail.com>
 * @license The MIT License (MIT)
 */

namespace Acme\Test;

/**
 * Test for BaseTrait with ImmutableTrait
 *
 * @author k.holy74@gmail.com
 */
class BaseTraitWithImmutableTraitTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructorDefensiveCopy()
	{
		$now = new \DateTime();
		$test = new BaseTraitWithImmutableTraitTestData([
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
		$test = new BaseTraitWithImmutableTraitTestData([
			'undefined_property' => 'Foo',
		]);
	}

	public function testIsset()
	{
		$test = new BaseTraitWithImmutableTraitTestData([
			'string' => 'Foo',
			'null'   => null,
		]);
		$this->assertTrue(isset($test->string));
		$this->assertFalse(isset($test->null));
		$this->assertFalse(isset($test->undefined_property));
	}

	public function testGet()
	{
		$test = new BaseTraitWithImmutableTraitTestData([
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
		$test = new BaseTraitWithImmutableTraitTestData();
		$test->undefined_property;
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetRaiseLogicException()
	{
		$test = new BaseTraitWithImmutableTraitTestData([
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
		$test = new BaseTraitWithImmutableTraitTestData();
		$test->undefined_property = 'Foo';
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testUnsetRaiseLogicException()
	{
		$test = new BaseTraitWithImmutableTraitTestData([
			'string' => 'Foo',
		]);
		unset($test->string);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testUnsetRaiseInvalidArgumentExceptionUndefinedProperty()
	{
		$test = new BaseTraitWithImmutableTraitTestData();
		unset($test->undefined_property);
	}

}
